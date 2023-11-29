<?php

declare(strict_types=1);

namespace O0h\KantanFw\Database;

use PDO;
use RuntimeException;

class Manager
{
    /** @var array<string, PDO> $connections */
    protected array $connections = [];

    /** @var array<string, string> $repositoryConnectionMap */
    protected array $repositoryConnectionMap = [];

    /**
     * @var array<string, Repository> $repositories
     */
    protected array $repositories = [];

    /**
     * @phpstan-param  array{dsn: string, user?: string, password?: string, options?: array<mixed>} $params
     */
    public function connect(string $name, array $params): void
    {
        $params = [
          ...[
            'dsn' => null,
            'user' => '',
            'password' => '',
            'options' => [],
          ],
          ...$params
        ];

        $con = new PDO(
            $params['dsn'],
            $params['user'],
            $params['password'],
            $params['options'],
        );

        $this->connections[$name] = $con;
    }

    public function getConnection(?string $name = null): PDO
    {
        if (count($this->connections) === 0) {
            throw new RuntimeException('接続情報が作成されていません');
        }
        if ($name === null) {
            return current($this->connections);
        }

        return $this->connections[$name] ?: throw new RuntimeException("コネクション'{$name}'は作成されていません");
    }

    /**
     * @param class-string<Repository> $repositoryName
     * @param string $name
     * @return void
     */
    public function setRepositoryConnectionMap(string $repositoryName, string $name): void
    {
        $this->repositoryConnectionMap[$repositoryName] = $name;
    }

    public function getConnectionForRepository(string $repositoryName): PDO
    {
        if (isset($this->repositoryConnectionMap[$repositoryName])) {
            $name = $this->repositoryConnectionMap[$repositoryName];
            $con = $this->getConnection($name);
        } else {
            $con = $this->getConnection();
        }

        return $con;
    }

    public function get(string $repositoryName): Repository
    {
        if (!isset($this->repositories[$repositoryName])) {
            $con = $this->getConnectionForRepository($repositoryName);

            /** @var class-string<Repository> $repositoryClass */
            $repositoryClass = $repositoryName.'Repository';
            $repository = new $repositoryClass($con);
            $this->repositories[$repositoryName] = $repository;
        }

        return $this->repositories[$repositoryName];
    }

    public function __destruct()
    {
        foreach ($this->repositories as $repository) {
            unset($repository);
        }

        foreach ($this->connections as $con) {
            unset($con);
        }
    }
}

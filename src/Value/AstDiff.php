<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Value;

final readonly class AstDiff extends Diff
{
    private const CHANGED_MARK_CHANGED = 'CHANGED';
    private const CHANGED_MARK_CHANGED_NOT_CHANGED = 'NOT_CHANGED';

    /** @var self::CHANGED_MARK_* */
    #[FieldName('ast-changed', 4)]
    private string $changed;

    public function __construct(
        #[FieldName('filename', 0)]
        public string $path,
        #[FieldName('status', 1)]
        public GitStatus $status,
        #[FieldName('BASE', 2)]
        public ?string $base,
        #[FieldName('HEAD', 3)]
        public ?string $head,
    ) {
        $this->changed = $this->hasChanged() ? self::CHANGED_MARK_CHANGED : self::CHANGED_MARK_CHANGED_NOT_CHANGED;
    }

    /**
     * Determines whether the base and head paths have changed as AST viewpoint.
     *
     * @return bool returns true if the base and head paths have changed, false otherwise
     */
    public function hasChanged(): bool
    {
        return $this->base !== $this->head;
    }

    /**
     * @return array{path: string, status: value-of<GitStatus>, base: string, head: string, changed: self::CHANGED_MARK_*}
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'status' => $this->status->value,
            'base' => $this->base ?? self::EMPTY_MARK,
            'head' => $this->head ?? self::EMPTY_MARK,
            'changed' => $this->changed,
        ];
    }
}

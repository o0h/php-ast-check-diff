<?php

declare(strict_types=1);

namespace O0h\PhpAstCheckDiff\Value;

readonly class Diff
{
    protected const EMPTY_MARK = '--';

    public function __construct(
        #[FieldName('filename', 0)]
        public string $path,
        #[FieldName('status', 1)]
        public GitStatus $status,
    ) {}

    /**
     * Retrieves the display fields for the current class.
     *
     * This method uses reflection to extract the properties of the class and
     * checks if they are decorated with the FieldName attribute. If a property
     * is decorated with this attribute, its order and name are added to the
     * display fields array.
     *
     * @return non-empty-list<string> an associative array where the keys are the order of the fields
     *                                and the values are the names of the fields
     */
    public static function getDisplayFields(): array
    {
        $reflection = new \ReflectionClass(static::class);
        $properties = $reflection->getProperties();

        $displayFields = [];
        foreach ($properties as $property) {
            $attribute = $property->getAttributes(FieldName::class);
            if (!$attribute) {
                continue;
            }
            \assert(1 === \count($attribute), 'Multiple FieldName found for ' . static::class);

            /** @var FieldName $fieldName */
            $fieldName = $attribute[0]->newInstance();
            $displayFields[$fieldName->order] = $fieldName->name;
        }
        \assert(\count($displayFields) > 0);
        ksort($displayFields);

        return array_values($displayFields);
    }

    /**
     * Converts the object to an array representation.
     *
     * @return array{path: string, status: value-of<GitStatus>}
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'status' => $this->status->value,
        ];
    }
}

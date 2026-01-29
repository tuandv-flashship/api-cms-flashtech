<?php

namespace App\Containers\AppSection\Tools\Supports\Import;

final class ImportColumn
{
    private string $label;
    private array $rules = [];
    private bool $nullable = false;
    private bool $boolean = false;
    private string $trueValue = 'Yes';
    private string $falseValue = 'No';

    private function __construct(private readonly string $name)
    {
        $this->label = $name;
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function rules(array $rules): self
    {
        $this->rules = $rules;
        $this->nullable = in_array('nullable', $rules, true);

        return $this;
    }

    public function boolean(string $trueValue = 'Yes', string $falseValue = 'No'): self
    {
        $this->boolean = true;
        $this->trueValue = $trueValue;
        $this->falseValue = $falseValue;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isBoolean(): bool
    {
        return $this->boolean;
    }

    public function getTrueValue(): string
    {
        return $this->trueValue;
    }

    public function getFalseValue(): string
    {
        return $this->falseValue;
    }
}

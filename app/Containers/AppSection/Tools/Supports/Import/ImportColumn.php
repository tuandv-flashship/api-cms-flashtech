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

    /**
     * Generate a human-readable description of the validation rules (i18n-aware).
     */
    public function getRuleDescription(): string
    {
        $parts = [];
        $isRequired = in_array('required', $this->rules, true);
        $isNullable = in_array('nullable', $this->rules, true);

        if ($isRequired) {
            $parts[] = __('data-synchronize.rules.required');
        }

        // Detect type
        if (in_array('string', $this->rules, true)) {
            $parts[] = __('data-synchronize.rules.string_type');
        } elseif (in_array('integer', $this->rules, true)) {
            $parts[] = __('data-synchronize.rules.integer_type');
        }

        // Max length
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'max:')) {
                $max = number_format((int) substr($rule, 4));
                $parts[] = __('data-synchronize.rules.max_chars', ['max' => $max]);
            }
        }

        // Boolean
        if ($this->boolean) {
            $parts[] = __('data-synchronize.rules.boolean_values', [
                'true' => $this->trueValue,
                'false' => $this->falseValue,
            ]);
        }

        // In rule
        foreach ($this->rules as $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\In) {
                $parts[] = __('data-synchronize.rules.allowed_values');
            }
        }

        $description = $parts !== [] ? implode(', ', $parts) : __('data-synchronize.rules.accepts_value');

        if ($isNullable || ! $isRequired) {
            $description .= '; ' . __('data-synchronize.rules.may_blank');
        }

        // Resolve label with i18n
        $colKey = "data-synchronize.columns.{$this->name}";
        $label = __($colKey) !== $colKey ? __($colKey) : $this->label;

        return __('data-synchronize.rule_template', [
            'label' => $label,
            'description' => $description,
        ]);
    }
}

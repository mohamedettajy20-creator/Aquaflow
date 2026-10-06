<?php
/**
 * Validator — lightweight rule-based input validator.
 * Usage:
 *   $v = new Validator($data);
 *   $v->required('email')->email('email')->required('password')->min('password', 8);
 *   if ($v->fails()) { $errors = $v->errors(); }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    private function value(string $field)
    {
        return $this->data[$field] ?? null;
    }

    public function required(string $field, string $label = null): static
    {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        $val = $this->value($field);
        if ($val === null || trim((string)$val) === '') {
            $this->errors[$field][] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field): static
    {
        $val = $this->value($field);
        if ($val && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'Please provide a valid email address.';
        }
        return $this;
    }

    public function min(string $field, int $length): static
    {
        $val = $this->value($field);
        if ($val && strlen((string)$val) < $length) {
            $this->errors[$field][] = "Must be at least {$length} characters.";
        }
        return $this;
    }

    public function max(string $field, int $length): static
    {
        $val = $this->value($field);
        if ($val && strlen((string)$val) > $length) {
            $this->errors[$field][] = "Must not exceed {$length} characters.";
        }
        return $this;
    }

    public function numeric(string $field): static
    {
        $val = $this->value($field);
        if ($val !== null && $val !== '' && !is_numeric($val)) {
            $this->errors[$field][] = 'Must be a number.';
        }
        return $this;
    }

    public function in(string $field, array $allowed): static
    {
        $val = $this->value($field);
        if ($val !== null && !in_array($val, $allowed, true)) {
            $this->errors[$field][] = 'Invalid value selected.';
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return null;
    }
}


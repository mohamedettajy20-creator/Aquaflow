<?php
class SettingModel extends Model
{
    protected string $table = 'settings';

    public function get(string $key, $default = null)
    {
        $row = $this->first(['setting_key' => $key]);
        return $row ? $row['setting_value'] : $default;
    }

    public function set(string $key, string $value, string $group = 'general'): void
    {
        $existing = $this->first(['setting_key' => $key]);
        if ($existing) {
            $this->update($existing['id'], ['setting_value' => $value]);
        } else {
            $this->create(['setting_key' => $key, 'setting_value' => $value, 'setting_group' => $group]);
        }
    }

    public function tierPricing(): array
    {
        $json = $this->get('tier_pricing', '[]');
        return json_decode($json, true) ?: [];
    }

    public function all(string $orderBy = null): array
    {
        return parent::all('setting_group, setting_key');
    }
}

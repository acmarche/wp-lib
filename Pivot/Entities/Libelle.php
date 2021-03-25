<?php


namespace AcMarche\Pivot\Entities;


class Libelle
{
    const FR = 'fr';
    const NL = 'nl';
    const EN = 'en';
    const DE = 'de';
    const DEFAULT = 'default';
    /**
     * @var array
     */
    public $languages;

    public function __construct()
    {
        $this->languages = [];
    }

    public function __toString()
    {
        return $this->libelle(self::FR);
    }

    public function add(?string $language, ?string $value)
    {
        $language = $language == '' ? self::DEFAULT : $language;
        $this->languages[$language] = $value;
    }

    public function get(string $language): ?string
    {
        return isset($this->languages[$language]) ? $this->languages[$language] : null;
    }

    public function libelle(string $language)
    {
        return isset($languages[$language]) ? $this->languages[$language] : $this->languages[self::DEFAULT];
    }
}

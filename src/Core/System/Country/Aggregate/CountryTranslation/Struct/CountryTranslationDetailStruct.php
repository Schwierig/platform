<?php declare(strict_types=1);

namespace Shopware\Core\System\Country\Aggregate\CountryTranslation\Struct;

use Shopware\Core\System\Language\Struct\LanguageBasicStruct;
use Shopware\Core\System\Country\Struct\CountryBasicStruct;

class CountryTranslationDetailStruct extends CountryTranslationBasicStruct
{
    /**
     * @var CountryBasicStruct
     */
    protected $country;

    /**
     * @var LanguageBasicStruct
     */
    protected $language;

    public function getCountry(): CountryBasicStruct
    {
        return $this->country;
    }

    public function setCountry(CountryBasicStruct $country): void
    {
        $this->country = $country;
    }

    public function getLanguage(): LanguageBasicStruct
    {
        return $this->language;
    }

    public function setLanguage(LanguageBasicStruct $language): void
    {
        $this->language = $language;
    }
}
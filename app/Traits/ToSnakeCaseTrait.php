<?php

namespace App\Traits;

trait ToSnakeCaseTrait
{
    public function toSnakeCase(string $str): string
    {
        $specialCharacterMap = ['ñ' => 'n',
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ç' => 'c',
            'à' => 'a',
            'è' => 'e',
            'ù' => 'u',
            'ê' => 'e',
            'î' => 'i',
            'ô' => 'o',
            'û' => 'u',
            'ä' => 'a',
            'ö' => 'o',
            'ü' => 'u',
            'ß' => 'ss',
            'ã' => 'a',
            'õ' => 'o',
            'i' => 'i',
            'ć' => 'c',
            'ń' => 'n',
            'ę' => 'e',
            'ł' => 'l',
            'ź' => 'z',
            'ś' => 's',
            'ř' => 'r',
            'ů' => 'u',
            'å' => 'a',
            'ø' => 'o',
            'æ' => 'ae',
            'ķ' => 'k',
            'ī' => 'i',
            'ż' => 'z',
            'ņ' => 'n',
            'ş' => 's',
            'ğ' => 'g',
            'ё' => 'yo',
            'ъ' => 'u',
            'ع' => 'a',
            'ح' => 'h',
            'Ξ' => 'X',
            'Ψ' => 'PS',
            'ח' => 'h',
            'ר' => 'r',
            'Ё' => 'Yo',
            'Ъ' => '',
            'Ь' => '',
        ];

        foreach ($specialCharacterMap as $specialChar => $replacement) {
            $str = str_replace($specialChar, $replacement, $str);
        }

        $str = preg_replace('/[^\w\s-]/', '', $str);  // Remove non-word characters except spaces and hyphens
        $str = preg_replace('/\s+/', '-', $str);       // Replace spaces with hyphens
        return strtolower($str);                      // Convert to lowercase
    }

}

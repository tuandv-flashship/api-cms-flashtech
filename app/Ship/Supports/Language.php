<?php

namespace App\Ship\Supports;

use Illuminate\Support\Facades\App;

class Language
{
    protected static array $flags = array(
        'ad' => 'Andorra',
        'ae' => 'United Arab Emirates',
        'af' => 'Afghanistan',
        'ag' => 'Antigua and Barbuda',
        'ai' => 'Anguilla',
        'al' => 'Albania',
        'am' => 'Armenia',
        'ao' => 'Angola',
        'ar' => 'Argentina',
        'as' => 'American Samoa',
        'at' => 'Austria',
        'au' => 'Australia',
        'aw' => 'Aruba',
        'ax' => 'Åland Islands',
        'az' => 'Azerbaijan',
        'ba' => 'Bosnia and Herzegovina',
        'bb' => 'Barbados',
        'bd' => 'Bangladesh',
        'be' => 'Belgium',
        'bf' => 'Burkina Faso',
        'bg' => 'Bulgaria',
        'bh' => 'Bahrain',
        'bi' => 'Burundi',
        'bj' => 'Benin',
        'bm' => 'Bermuda',
        'bn' => 'Brunei',
        'bo' => 'Bolivia',
        'br' => 'Brazil',
        'bs' => 'Bahamas',
        'bt' => 'Bhutan',
        'bw' => 'Botswana',
        'by' => 'Belarus',
        'bz' => 'Belize',
        'ca' => 'Canada',
        'ca_ES' => 'Catalonia',
        'cc' => 'Cocos',
        'cd' => 'Democratic Republic of the Congo',
        'cf' => 'Central African Republic',
        'cg' => 'Congo',
        'ch' => 'Switzerland',
        'ci' => 'Ivory Coast',
        'ck' => 'Cook Islands',
        'cl' => 'Chile',
        'cm' => 'Cameroon',
        'cn' => 'China',
        'co' => 'Colombia',
        'cr' => 'Costa Rica',
        'cu' => 'Cuba',
        'cv' => 'Cape Verde',
        'cx' => 'Christmas Island',
        'cy' => 'Cyprus',
        'cz' => 'Czech Republic',
        'de' => 'Germany',
        'dj' => 'Djibouti',
        'dk' => 'Denmark',
        'dm' => 'Dominica',
        'do' => 'Dominican Republic',
        'dz' => 'Algeria',
        'ec' => 'Ecuador',
        'ee' => 'Estonia',
        'eg' => 'Egypt',
        'eh' => 'Western Sahara',
        'gb-eng' => 'England',
        'er' => 'Eritrea',
        'es' => 'Spain',
        'et' => 'Ethiopia',
        'fi' => 'Finland',
        'fj' => 'Fiji',
        'fk' => 'Falkland Islands',
        'fm' => 'Micronesia',
        'fo' => 'Faroe Islands',
        'fr' => 'France',
        'ga' => 'Gabon',
        'gb' => 'United Kingdom',
        'gd' => 'Grenada',
        'ge' => 'Georgia',
        'gh' => 'Ghana',
        'gi' => 'Gibraltar',
        'gl' => 'Greenland',
        'gm' => 'Gambia',
        'gn' => 'Guinea',
        'gp' => 'Guadeloupe',
        'gq' => 'Equatorial Guinea',
        'gr' => 'Greece',
        'gs' => 'South Georgia and the South Sandwich Islands',
        'gt' => 'Guatemala',
        'gu' => 'Guam',
        'gw' => 'Guinea-Bissau',
        'gy' => 'Guyana',
        'hk' => 'Hong Kong',
        'hm' => 'Heard Island and McDonald Islands',
        'hn' => 'Honduras',
        'hr' => 'Croatia',
        'ht' => 'Haiti',
        'hu' => 'Hungary',
        'id' => 'Indonesia',
        'ie' => 'Republic of Ireland',
        'il' => 'Israel',
        'in' => 'India',
        'io' => 'British Indian Ocean Territory',
        'iq' => 'Iraq',
        'ir' => 'Iran',
        'is' => 'Iceland',
        'it' => 'Italy',
        'jm' => 'Jamaica',
        'jo' => 'Jordan',
        'jp' => 'Japan',
        'ke' => 'Kenya',
        'kg' => 'Kyrgyzstan',
        'kh' => 'Cambodia',
        'ki' => 'Kiribati',
        'km' => 'Comoros',
        'kn' => 'Saint Kitts and Nevis',
        'kp' => 'North Korea',
        'kr' => 'South Korea',
        'kw' => 'Kuwait',
        'ky' => 'Cayman Islands',
        'kz' => 'Kazakhstan',
        'la' => 'Laos',
        'lb' => 'Lebanon',
        'lc' => 'Saint Lucia',
        'li' => 'Liechtenstein',
        'lk' => 'Sri Lanka',
        'lr' => 'Liberia',
        'ls' => 'Lesotho',
        'lt' => 'Lithuania',
        'lu' => 'Luxembourg',
        'lv' => 'Latvia',
        'ly' => 'Libya',
        'ma' => 'Morocco',
        'mc' => 'Monaco',
        'md' => 'Moldova',
        'me' => 'Montenegro',
        'mg' => 'Madagascar',
        'mh' => 'Marshall Islands',
        'mk' => 'Macedonia',
        'ml' => 'Mali',
        'mm' => 'Myanmar',
        'mn' => 'Mongolia',
        'mo' => 'Macao',
        'mp' => 'Northern Mariana Islands',
        'mq' => 'Martinique',
        'mr' => 'Mauritania',
        'ms' => 'Montserrat',
        'mt' => 'Malta',
        'mu' => 'Mauritius',
        'mv' => 'Maldives',
        'mw' => 'Malawi',
        'mx' => 'Mexico',
        'my' => 'Malaysia',
        'mz' => 'Mozambique',
        'na' => 'Namibia',
        'nc' => 'New Caledonia',
        'ne' => 'Niger',
        'nf' => 'Norfolk Island',
        'ng' => 'Nigeria',
        'ni' => 'Nicaragua',
        'nl' => 'Netherlands',
        'no' => 'Norway',
        'np' => 'Nepal',
        'nr' => 'Nauru',
        'nu' => 'Niue',
        'nz' => 'New Zealand',
        'om' => 'Oman',
        'pa' => 'Panama',
        'pe' => 'Peru',
        'pf' => 'French Polynesia',
        'pg' => 'Papua New Guinea',
        'ph' => 'Philippines',
        'pk' => 'Pakistan',
        'pl' => 'Poland',
        'pm' => 'Saint Pierre and Miquelon',
        'pn' => 'Pitcairn',
        'pr' => 'Puerto Rico',
        'ps' => 'Palestinian Territory',
        'pt' => 'Portugal',
        'pw' => 'Belau',
        'py' => 'Paraguay',
        'qa' => 'Qatar',
        'ro' => 'Romania',
        'rs' => 'Serbia',
        'ru' => 'Russia',
        'rw' => 'Rwanda',
        'sa' => 'Saudi Arabia',
        'sb' => 'Solomon Islands',
        'sc' => 'Seychelles',
        'gb-sct' => 'Scotland',
        'sd' => 'Sudan',
        'se' => 'Sweden',
        'sg' => 'Singapore',
        'sh' => 'Saint Helena',
        'si' => 'Slovenia',
        'sk' => 'Slovakia',
        'sl' => 'Sierra Leone',
        'sm' => 'San Marino',
        'sn' => 'Senegal',
        'so' => 'Somalia',
        'sr' => 'Suriname',
        'ss' => 'South Sudan',
        'st' => 'São Tomé and Príncipe',
        'sv' => 'El Salvador',
        'sy' => 'Syria',
        'sz' => 'Swaziland',
        'tc' => 'Turks and Caicos Islands',
        'td' => 'Chad',
        'tf' => 'French Southern Territories',
        'tg' => 'Togo',
        'th' => 'Thailand',
        'tj' => 'Tajikistan',
        'tk' => 'Tokelau',
        'tl' => 'Timor-Leste',
        'tm' => 'Turkmenistan',
        'tn' => 'Tunisia',
        'to' => 'Tonga',
        'tr' => 'Turkey',
        'tt' => 'Trinidad and Tobago',
        'tv' => 'Tuvalu',
        'tw' => 'Taiwan',
        'tz' => 'Tanzania',
        'ua' => 'Ukraine',
        'ug' => 'Uganda',
        'us' => 'United States',
        'uy' => 'Uruguay',
        'uz' => 'Uzbekistan',
        'va' => 'Vatican',
        'vc' => 'Saint Vincent and the Grenadines',
        've' => 'Venezuela',
        'vg' => 'British Virgin Islands',
        'vi' => 'United States Virgin Islands',
        'vn' => 'Vietnam',
        'vu' => 'Vanuatu',
        'gb-wls' => 'Wales',
        'wf' => 'Wallis and Futuna',
        'ws' => 'Western Samoa',
        'ye' => 'Yemen',
        'yt' => 'Mayotte',
        'za' => 'South Africa',
        'zm' => 'Zambia',
        'zw' => 'Zimbabwe',
    );
    protected static array $languages = array(
        'af' =>
        array(
            0 => 'af',
            1 => 'af',
            2 => 'Afrikaans',
            3 => 'ltr',
            4 => 'za',
        ),
        'am' =>
        array(
            0 => 'am',
            1 => 'am',
            2 => 'አማርኛ',
            3 => 'ltr',
            4 => 'et',
        ),
        'ar' =>
        array(
            0 => 'ar',
            1 => 'ar',
            2 => 'العربية',
            3 => 'rtl',
            4 => 'ar',
        ),
        'ary' =>
        array(
            0 => 'ar',
            1 => 'ary',
            2 => 'العربية المغربية',
            3 => 'rtl',
            4 => 'ma',
        ),
        'az' =>
        array(
            0 => 'az',
            1 => 'az',
            2 => 'Azərbaycan',
            3 => 'ltr',
            4 => 'az',
        ),
        'azb' =>
        array(
            0 => 'az',
            1 => 'azb',
            2 => 'گؤنئی آذربایجان',
            3 => 'rtl',
            4 => 'az',
        ),
        'bel' =>
        array(
            0 => 'be',
            1 => 'bel',
            2 => 'Беларуская мова',
            3 => 'ltr',
            4 => 'by',
        ),
        'bg_BG' =>
        array(
            0 => 'bg',
            1 => 'bg_BG',
            2 => 'български',
            3 => 'ltr',
            4 => 'bg',
        ),
        'bn_BD' =>
        array(
            0 => 'bn',
            1 => 'bn_BD',
            2 => 'বাংলা',
            3 => 'ltr',
            4 => 'bd',
        ),
        'bo' =>
        array(
            0 => 'bo',
            1 => 'bo',
            2 => 'བོད་སྐད',
            3 => 'ltr',
            4 => 'tibet',
        ),
        'bs_BA' =>
        array(
            0 => 'bs',
            1 => 'bs_BA',
            2 => 'Bosanski',
            3 => 'ltr',
            4 => 'ba',
        ),
        'ca' =>
        array(
            0 => 'ca',
            1 => 'ca_ES',
            2 => 'Catalan',
            3 => 'ltr',
            4 => 'es',
        ),
        'ceb' =>
        array(
            0 => 'ceb',
            1 => 'ceb',
            2 => 'Cebuano',
            3 => 'ltr',
            4 => 'ph',
        ),
        'cs_CZ' =>
        array(
            0 => 'cs',
            1 => 'cs_CZ',
            2 => 'Čeština',
            3 => 'ltr',
            4 => 'cz',
        ),
        'cy' =>
        array(
            0 => 'cy',
            1 => 'cy',
            2 => 'Cymraeg',
            3 => 'ltr',
            4 => 'gb-wls',
        ),
        'da_DK' =>
        array(
            0 => 'da',
            1 => 'da_DK',
            2 => 'Dansk',
            3 => 'ltr',
            4 => 'dk',
        ),
        'de_CH' =>
        array(
            0 => 'de',
            1 => 'de_CH',
            2 => 'Deutsch',
            3 => 'ltr',
            4 => 'ch',
        ),
        'de_CH_informal' =>
        array(
            0 => 'de',
            1 => 'de_CH_informal',
            2 => 'Deutsch',
            3 => 'ltr',
            4 => 'ch',
        ),
        'de_DE' =>
        array(
            0 => 'de',
            1 => 'de_DE',
            2 => 'Deutsch',
            3 => 'ltr',
            4 => 'de',
        ),
        'de_DE_formal' =>
        array(
            0 => 'de',
            1 => 'de_DE_formal',
            2 => 'Deutsch',
            3 => 'ltr',
            4 => 'de',
        ),
        'el' =>
        array(
            0 => 'el',
            1 => 'el',
            2 => 'Ελληνικά',
            3 => 'ltr',
            4 => 'gr',
        ),
        'en_US' =>
        array(
            0 => 'en',
            1 => 'en_US',
            2 => 'English',
            3 => 'ltr',
            4 => 'us',
        ),
        'en_AU' =>
        array(
            0 => 'en',
            1 => 'en_AU',
            2 => 'English',
            3 => 'ltr',
            4 => 'au',
        ),
        'en_CA' =>
        array(
            0 => 'en',
            1 => 'en_CA',
            2 => 'English',
            3 => 'ltr',
            4 => 'ca',
        ),
        'en_GB' =>
        array(
            0 => 'en',
            1 => 'en_GB',
            2 => 'English',
            3 => 'ltr',
            4 => 'gb',
        ),
        'en_NZ' =>
        array(
            0 => 'en',
            1 => 'en_NZ',
            2 => 'English',
            3 => 'ltr',
            4 => 'nz',
        ),
        'en_ZA' =>
        array(
            0 => 'en',
            1 => 'en_ZA',
            2 => 'English',
            3 => 'ltr',
            4 => 'za',
        ),
        'es_AR' =>
        array(
            0 => 'es',
            1 => 'es_AR',
            2 => 'Español',
            3 => 'ltr',
            4 => 'ar',
        ),
        'es_CL' =>
        array(
            0 => 'es',
            1 => 'es_CL',
            2 => 'Español',
            3 => 'ltr',
            4 => 'cl',
        ),
        'es_CO' =>
        array(
            0 => 'es',
            1 => 'es_CO',
            2 => 'Español',
            3 => 'ltr',
            4 => 'co',
        ),
        'es_ES' =>
        array(
            0 => 'es',
            1 => 'es_ES',
            2 => 'Español',
            3 => 'ltr',
            4 => 'es',
        ),
        'es_GT' =>
        array(
            0 => 'es',
            1 => 'es_GT',
            2 => 'Español',
            3 => 'ltr',
            4 => 'gt',
        ),
        'es_MX' =>
        array(
            0 => 'es',
            1 => 'es_MX',
            2 => 'Español',
            3 => 'ltr',
            4 => 'mx',
        ),
        'es_PE' =>
        array(
            0 => 'es',
            1 => 'es_PE',
            2 => 'Español',
            3 => 'ltr',
            4 => 'pe',
        ),
        'es_VE' =>
        array(
            0 => 'es',
            1 => 'es_VE',
            2 => 'Español',
            3 => 'ltr',
            4 => 've',
        ),
        'et' =>
        array(
            0 => 'et',
            1 => 'et',
            2 => 'Eesti',
            3 => 'ltr',
            4 => 'ee',
        ),
        'eu' =>
        array(
            0 => 'eu',
            1 => 'eu',
            2 => 'Euskara',
            3 => 'ltr',
            4 => 'fr',
        ),
        'fa_AF' =>
        array(
            0 => 'fa',
            1 => 'fa_AF',
            2 => 'فارسی',
            3 => 'rtl',
            4 => 'af',
        ),
        'fa_IR' =>
        array(
            0 => 'fa',
            1 => 'fa_IR',
            2 => 'فارسی',
            3 => 'rtl',
            4 => 'ir',
        ),
        'fi' =>
        array(
            0 => 'fi',
            1 => 'fi',
            2 => 'Suomi',
            3 => 'ltr',
            4 => 'fi',
        ),
        'fo' =>
        array(
            0 => 'fo',
            1 => 'fo',
            2 => 'Føroyskt',
            3 => 'ltr',
            4 => 'fo',
        ),
        'fr_BE' =>
        array(
            0 => 'fr',
            1 => 'fr_BE',
            2 => 'Français',
            3 => 'ltr',
            4 => 'be',
        ),
        'fr_FR' =>
        array(
            0 => 'fr',
            1 => 'fr_FR',
            2 => 'Français',
            3 => 'ltr',
            4 => 'fr',
        ),
        'fy' =>
        array(
            0 => 'fy',
            1 => 'fy',
            2 => 'Frysk',
            3 => 'ltr',
            4 => 'nl',
        ),
        'gd' =>
        array(
            0 => 'gd',
            1 => 'gd',
            2 => 'Gàidhlig',
            3 => 'ltr',
            4 => 'gb-sct',
        ),
        'gl_ES' =>
        array(
            0 => 'gl',
            1 => 'gl_ES',
            2 => 'Galego',
            3 => 'ltr',
            4 => 'gl',
        ),
        'gu' =>
        array(
            0 => 'gu',
            1 => 'gu',
            2 => 'ગુજરાતી',
            3 => 'ltr',
            4 => 'in',
        ),
        'haz' =>
        array(
            0 => 'haz',
            1 => 'haz',
            2 => 'هزاره گی',
            3 => 'rtl',
            4 => 'af',
        ),
        'he_IL' =>
        array(
            0 => 'he',
            1 => 'he_IL',
            2 => 'עברית',
            3 => 'rtl',
            4 => 'il',
        ),
        'hi_IN' =>
        array(
            0 => 'hi',
            1 => 'hi_IN',
            2 => 'हिन्दी',
            3 => 'ltr',
            4 => 'in',
        ),
        'hr' =>
        array(
            0 => 'hr',
            1 => 'hr',
            2 => 'Hrvatski',
            3 => 'ltr',
            4 => 'hr',
        ),
        'ht' =>
        array(
            0 => 'ht',
            1 => 'ht',
            2 => 'Kreyòl Ayisyen',
            3 => 'ltr',
            4 => 'ht',
        ),
        'hu_HU' =>
        array(
            0 => 'hu',
            1 => 'hu_HU',
            2 => 'Magyar',
            3 => 'ltr',
            4 => 'hu',
        ),
        'hy' =>
        array(
            0 => 'hy',
            1 => 'hy',
            2 => 'Հայերեն',
            3 => 'ltr',
            4 => 'am',
        ),
        'id_ID' =>
        array(
            0 => 'id',
            1 => 'id_ID',
            2 => 'Bahasa Indonesia',
            3 => 'ltr',
            4 => 'id',
        ),
        'is_IS' =>
        array(
            0 => 'is',
            1 => 'is_IS',
            2 => 'Íslenska',
            3 => 'ltr',
            4 => 'is',
        ),
        'it_IT' =>
        array(
            0 => 'it',
            1 => 'it_IT',
            2 => 'Italiano',
            3 => 'ltr',
            4 => 'it',
        ),
        'ja' =>
        array(
            0 => 'ja',
            1 => 'ja',
            2 => '日本語',
            3 => 'ltr',
            4 => 'jp',
        ),
        'jv_ID' =>
        array(
            0 => 'jv',
            1 => 'jv_ID',
            2 => 'Basa Jawa',
            3 => 'ltr',
            4 => 'id',
        ),
        'ka_GE' =>
        array(
            0 => 'ka',
            1 => 'ka_GE',
            2 => 'ქართული',
            3 => 'ltr',
            4 => 'ge',
        ),
        'kk' =>
        array(
            0 => 'kk',
            1 => 'kk',
            2 => 'Қазақ тілі',
            3 => 'ltr',
            4 => 'kz',
        ),
        'kh' =>
        array(
            0 => 'kh',
            1 => 'kh',
            2 => 'Cambodia',
            3 => 'ltr',
            4 => 'kh',
        ),
        'ko_KR' =>
        array(
            0 => 'ko',
            1 => 'ko_KR',
            2 => '한국어',
            3 => 'ltr',
            4 => 'kr',
        ),
        'ky_KG' =>
        array(
            0 => 'ky',
            1 => 'ky_KG',
            2 => 'Кыргызча',
            3 => 'ltr',
            4 => 'kg',
        ),
        'ckb' =>
        array(
            0 => 'ku',
            1 => 'ckb',
            2 => 'کوردی',
            3 => 'rtl',
            4 => 'kurdistan',
        ),
        'lo' =>
        array(
            0 => 'lo',
            1 => 'lo',
            2 => 'ພາສາລາວ',
            3 => 'ltr',
            4 => 'la',
        ),
        'lt_LT' =>
        array(
            0 => 'lt',
            1 => 'lt_LT',
            2 => 'Lietuviškai',
            3 => 'ltr',
            4 => 'lt',
        ),
        'lv' =>
        array(
            0 => 'lv',
            1 => 'lv',
            2 => 'Latviešu valoda',
            3 => 'ltr',
            4 => 'lv',
        ),
        'mk_MK' =>
        array(
            0 => 'mk',
            1 => 'mk_MK',
            2 => 'македонски јазик',
            3 => 'ltr',
            4 => 'mk',
        ),
        'mn' =>
        array(
            0 => 'mn',
            1 => 'mn',
            2 => 'Монгол хэл',
            3 => 'ltr',
            4 => 'mn',
        ),
        'mr' =>
        array(
            0 => 'mr',
            1 => 'mr',
            2 => 'मराठी',
            3 => 'ltr',
            4 => 'in',
        ),
        'ms_MY' =>
        array(
            0 => 'ms',
            1 => 'ms_MY',
            2 => 'Bahasa Melayu',
            3 => 'ltr',
            4 => 'my',
        ),
        'my_MM' =>
        array(
            0 => 'my',
            1 => 'my_MM',
            2 => 'ဗမာစာ',
            3 => 'ltr',
            4 => 'mm',
        ),
        'mv' =>
        array(
            0 => 'mv',
            1 => 'mv',
            2 => 'Maldives',
            3 => 'rtl',
            4 => 'mv',
        ),
        'nb_NO' =>
        array(
            0 => 'nb',
            1 => 'nb_NO',
            2 => 'Norsk Bokmål',
            3 => 'ltr',
            4 => 'no',
        ),
        'ne_NP' =>
        array(
            0 => 'ne',
            1 => 'ne_NP',
            2 => 'नेपाली',
            3 => 'ltr',
            4 => 'np',
        ),
        'nl_NL' =>
        array(
            0 => 'nl',
            1 => 'nl_NL',
            2 => 'Nederlands',
            3 => 'ltr',
            4 => 'nl',
        ),
        'nl_NL_formal' =>
        array(
            0 => 'nl',
            1 => 'nl_NL_formal',
            2 => 'Nederlands',
            3 => 'ltr',
            4 => 'nl',
        ),
        'nn_NO' =>
        array(
            0 => 'nn',
            1 => 'nn_NO',
            2 => 'Norsk Nynorsk',
            3 => 'ltr',
            4 => 'no',
        ),
        'pl_PL' =>
        array(
            0 => 'pl',
            1 => 'pl_PL',
            2 => 'Polski',
            3 => 'ltr',
            4 => 'pl',
        ),
        'ps' =>
        array(
            0 => 'ps',
            1 => 'ps',
            2 => 'پښتو',
            3 => 'rtl',
            4 => 'af',
        ),
        'pt_BR' =>
        array(
            0 => 'pt',
            1 => 'pt_BR',
            2 => 'Português',
            3 => 'ltr',
            4 => 'br',
        ),
        'pt_PT' =>
        array(
            0 => 'pt',
            1 => 'pt_PT',
            2 => 'Português',
            3 => 'ltr',
            4 => 'pt',
        ),
        'ro_RO' =>
        array(
            0 => 'ro',
            1 => 'ro_RO',
            2 => 'Română',
            3 => 'ltr',
            4 => 'ro',
        ),
        'ru_RU' =>
        array(
            0 => 'ru',
            1 => 'ru_RU',
            2 => 'Русский',
            3 => 'ltr',
            4 => 'ru',
        ),
        'si_LK' =>
        array(
            0 => 'si',
            1 => 'si_LK',
            2 => 'සිංහල',
            3 => 'ltr',
            4 => 'lk',
        ),
        'sk_SK' =>
        array(
            0 => 'sk',
            1 => 'sk_SK',
            2 => 'Slovenčina',
            3 => 'ltr',
            4 => 'sk',
        ),
        'sl_SI' =>
        array(
            0 => 'sl',
            1 => 'sl_SI',
            2 => 'Slovenščina',
            3 => 'ltr',
            4 => 'si',
        ),
        'so_SO' =>
        array(
            0 => 'so',
            1 => 'so_SO',
            2 => 'Af-Soomaali',
            3 => 'ltr',
            4 => 'so',
        ),
        'sq' =>
        array(
            0 => 'sq',
            1 => 'sq',
            2 => 'Shqip',
            3 => 'ltr',
            4 => 'al',
        ),
        'sq_AL' =>
        array(
            0 => 'sq',
            1 => 'sq_AL',
            2 => 'Shqip (Shqipëri)',
            3 => 'ltr',
            4 => 'al',
        ),
        'sr_RS' =>
        array(
            0 => 'sr',
            1 => 'sr_RS',
            2 => 'Српски језик',
            3 => 'ltr',
            4 => 'rs',
        ),
        'su_ID' =>
        array(
            0 => 'su',
            1 => 'su_ID',
            2 => 'Basa Sunda',
            3 => 'ltr',
            4 => 'id',
        ),
        'sv_SE' =>
        array(
            0 => 'sv',
            1 => 'sv_SE',
            2 => 'Svenska',
            3 => 'ltr',
            4 => 'se',
        ),
        'szl' =>
        array(
            0 => 'szl',
            1 => 'szl',
            2 => 'Ślōnskŏ gŏdka',
            3 => 'ltr',
            4 => 'pl',
        ),
        'sw' =>
        array(
            0 => 'sw',
            1 => 'sw',
            2 => 'Swahili',
            3 => 'ltr',
            4 => 'tz',
        ),
        'ta_LK' =>
        array(
            0 => 'ta',
            1 => 'ta_LK',
            2 => 'தமிழ்',
            3 => 'ltr',
            4 => 'lk',
        ),
        'th' =>
        array(
            0 => 'th',
            1 => 'th',
            2 => 'ไทย',
            3 => 'ltr',
            4 => 'th',
        ),
        'ti' =>
        array(
            0 => 'ti',
            1 => 'ti',
            2 => 'ትግርኛ',
            3 => 'ltr',
            4 => 'er',
        ),
        'tl' =>
        array(
            0 => 'tl',
            1 => 'tl',
            2 => 'Tagalog',
            3 => 'ltr',
            4 => 'ph',
        ),
        'tr_TR' =>
        array(
            0 => 'tr',
            1 => 'tr_TR',
            2 => 'Türkçe',
            3 => 'ltr',
            4 => 'tr',
        ),
        'ug_CN' =>
        array(
            0 => 'ug',
            1 => 'ug_CN',
            2 => 'Uyƣurqə',
            3 => 'ltr',
            4 => 'cn',
        ),
        'uk' =>
        array(
            0 => 'uk',
            1 => 'uk',
            2 => 'Українська',
            3 => 'ltr',
            4 => 'ua',
        ),
        'ur' =>
        array(
            0 => 'ur',
            1 => 'ur',
            2 => 'اردو',
            3 => 'rtl',
            4 => 'pk',
        ),
        'uz_UZ' =>
        array(
            0 => 'uz',
            1 => 'uz_UZ',
            2 => 'Oʻzbek',
            3 => 'ltr',
            4 => 'uz',
        ),
        'vi' =>
        array(
            0 => 'vi',
            1 => 'vi',
            2 => 'Tiếng Việt',
            3 => 'ltr',
            4 => 'vn',
        ),
        'zh_CN' =>
        array(
            0 => 'zh',
            1 => 'zh_CN',
            2 => '中文 (中国)',
            3 => 'ltr',
            4 => 'cn',
        ),
        'zh_HK' =>
        array(
            0 => 'zh',
            1 => 'zh_HK',
            2 => '中文 (香港)',
            3 => 'ltr',
            4 => 'hk',
        ),
        'zh_TW' =>
        array(
            0 => 'zh',
            1 => 'zh_TW',
            2 => '中文 (台灣)',
            3 => 'ltr',
            4 => 'tw',
        ),
        'tg' =>
        array(
            0 => 'tg',
            1 => 'tg',
            2 => 'Tajik',
            3 => 'ltr',
            4 => 'tj',
        ),
    );

    public static function getListLanguageFlags(): array
    {
        return self::$flags;
    }

    public static function getAvailableLocales(bool $original = false): array
    {
        $locales = [];

        foreach (self::$languages as $key => $language) {
            if (! is_array($language) || count($language) < 5) {
                continue;
            }

            $locales[$key] = [
                'locale' => $language[0],
                'code' => $language[1],
                'name' => $language[2],
                'flag' => $language[4] ?? $language[0],
                'is_rtl' => ($language[3] ?? 'ltr') === 'rtl',
            ];
        }

        return $locales;
    }

    public static function getListLanguages(): array
    {
        return self::$languages;
    }

    public static function getDefaultLanguage(): array
    {
        $available = static::getAvailableLocales(true);

        $preferredLocales = [
            config('app.locale', 'en'),
            config('app.fallback_locale', 'en'),
        ];

        foreach ($preferredLocales as $locale) {
            if (! $locale) {
                continue;
            }

            $normalized = str_replace('-', '_', $locale);

            if (isset($available[$locale])) {
                return $available[$locale];
            }

            if (isset($available[$normalized])) {
                return $available[$normalized];
            }

            foreach ($available as $language) {
                if (($language['locale'] ?? null) === $locale || ($language['locale'] ?? null) === $normalized) {
                    return $language;
                }
            }
        }

        if (isset($available['en_US'])) {
            return $available['en_US'];
        }

        if (! empty($available)) {
            return reset($available);
        }

        return [
            'locale' => 'en',
            'code' => 'en_US',
            'name' => 'English',
            'flag' => 'us',
            'is_rtl' => false,
        ];
    }

    public static function getLocales(): array
    {
        $locales = [];

        foreach (static::getListLanguages() as $language) {
            if (! is_array($language) || count($language) < 3) {
                continue;
            }

            $locale = $language[0] ?? null;
            $name = $language[2] ?? null;

            if (! $locale || ! $name) {
                continue;
            }

            if (! array_key_exists($locale, $locales)) {
                $locales[$locale] = $name;
            }
        }

        $locales = [
            ...$locales,
            'de_CH' => 'Deutsch (Schweiz)',
            'pt_BR' => 'Português (Brasil)',
            'sr_Cyrl' => 'Српски (ћирилица)',
            'sr_Latn' => 'Srpski (latinica)',
            'sr_Latn_ME' => 'Srpski (latinica, Crna Gora)',
            'uz_Cyrl' => 'Ўзбек (Ўзбекистон)',
            'uz_Latn' => 'O‘zbek',
            'zh_CN' => '中文 (中国)',
            'zh_TW' => '中文 (台灣)',
            'zh_HK' => '中文 (香港)',
        ];

        ksort($locales);

        return $locales;
    }

    public static function getLocaleKeys(): array
    {
        return array_unique(array_keys(static::getLocales()));
    }

    public static function getLanguageCodes(): array
    {
        $codes = [];

        foreach (static::getListLanguages() as $language) {
            if (! is_array($language)) {
                continue;
            }

            $code = $language[1] ?? null;

            if ($code) {
                $codes[] = $code;
            }
        }

        return array_values(array_unique($codes));
    }

    public static function getCurrentLocale(): array
    {
        $available = static::getAvailableLocales(true);
        $currentLocale = App::getLocale();

        if (isset($available[$currentLocale])) {
            return $available[$currentLocale];
        }

        $normalized = str_replace('-', '_', $currentLocale);

        if (isset($available[$normalized])) {
            return $available[$normalized];
        }

        foreach ($available as $language) {
            if (($language['locale'] ?? null) === $currentLocale || ($language['locale'] ?? null) === $normalized) {
                return $language;
            }
        }

        return static::getDefaultLanguage();
    }
}

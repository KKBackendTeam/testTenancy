<?php

use Carbon\Carbon;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Agency;
use App\Models\Tenancy;

/**
 *  Short methods for fetch User Information
 */

function superAdmin()
{
    return User::where('roleStatus', 2)->firstOrFail();
}

function authUser()
{
    return JWTAuth::parseToken()->authenticate();
}

function authAgencyId()
{
    return JWTAuth::parseToken()->authenticate()->agency_id;
}

function authUserId()
{
    return JWTAuth::parseToken()->authenticate()->id;
}

function agencyData()
{
    return Agency::where('id', authAgencyId())->first();
}

function agencyDataFormId($id)
{
    return Agency::where('id', $id)->first();
}

function authUserData()
{
    return User::where('id', authUserId())->first();
}

function agencyAdmin()
{
    return JWTAuth::parseToken()->authenticate()->roleStatus == 1 ? true : false;
}

function agencyAdminUserById($id)
{
    return User::where('agency_id', $id)->where('roleStatus', 1)->first();
}

function agencyStaff()
{
    return JWTAuth::parseToken()->authenticate()->roleStatus == 0 ? true : false;
}

function someThingIsCookingHere()
{
    return '<h1><br><p style="margin-left:400px; margin-top:200px">#...Happy ("-") Ending 31/05<p><h1>';
}

function dateFormat($date)
{
    return Carbon::parse($date);
}

function getUniqueReference($reference, $startDate)
{
    return $reference . '_' . Carbon::parse($startDate)->format('Y-m-d');
}

function paginationNumber($pageSize)
{
    return is_null($pageSize) ? 5 : $pageSize;
}

function checkForUniqueValues($arrayOfValues)
{
    $duplicateArray = $duplicateArrayOfKeys = [];
    $duplicateArray = $arrayOfValues;
    foreach ($duplicateArray as $key => $d) {
        unset($duplicateArray[$key]);
        if (in_array($d, $duplicateArray)) {
            $duplicateArrayOfKeys[] = ($key + 1);
        }
        $duplicateArray = $arrayOfValues;
    }

    return $duplicateArrayOfKeys;
}

function addAgencyIdInRequest($request, $authAgencyId)
{
    $data = $request->all();
    $data['agency_id'] = $authAgencyId;
    return $data;
}

function convertTimestampFormat($date)
{
    return Carbon::parse($date)->format('jS F Y') . ' at ' . Carbon::parse($date)->format('g:i A');
}

function timeChangeAccordingToTimezone($timezone)
{
    try {
        return now()->setTimezone(new \DateTimeZone($timezone));
    } catch (Exception $e) {
        return now()->setTimezone(new \DateTimeZone('UTC'));
    }
}

function timeChangeAccordingToTimezoneForChasing($timezone, $response, $stalledTime)
{
    $min = (int) ($response * 60);
    try {
        return (now()->addMinutes($min))->setTimezone(new \DateTimeZone($timezone));
    } catch (Exception $e) {
        return (now()->addMinutes($min))->setTimezone(new \DateTimeZone('UTC'));
    }
}

function isPastChecker($date, $timezone)
{
    $endTime = strtotime(Carbon::parse($date));
    $timezoneNow = strtotime(now()->setTimezone(new \DateTimeZone($timezone)));

    return ($endTime < $timezoneNow) ? true : false;
}

function touchTenancy($tenancyId, $timezone)
{
    $t = Tenancy::find($tenancyId);
    if (!isset($t->timezone) || $t->timezone == 'UTC') {
        $t->timezone = $timezone;
    }
    $t->updated_at = timeChangeAccordingToTimezone($timezone);
    $t->save();
    return true;
}

if (!function_exists('countryToDialingCode')) {
    function countryToDialingCode($countryCode)
    {
        $countryCodes = [
            'AF' => '+93', // Afghanistan
            'AL' => '+355', // Albania
            'DZ' => '+213', // Algeria
            'AS' => '+1', // American Samoa
            'AD' => '+376', // Andorra
            'AO' => '+244', // Angola
            'AI' => '+1', // Anguilla
            'AG' => '+1', // Antigua and Barbuda
            'AR' => '+54', // Argentina
            'AM' => '+374', // Armenia
            'AW' => '+297', // Aruba
            'AU' => '+61', // Australia
            'AT' => '+43', // Austria
            'AZ' => '+994', // Azerbaijan
            'BS' => '+1', // Bahamas
            'BH' => '+973', // Bahrain
            'BD' => '+880', // Bangladesh
            'BB' => '+1', // Barbados
            'BY' => '+375', // Belarus
            'BE' => '+32', // Belgium
            'BZ' => '+501', // Belize
            'BJ' => '+229', // Benin
            'BM' => '+1', // Bermuda
            'BT' => '+975', // Bhutan
            'BO' => '+591', // Bolivia
            'BA' => '+387', // Bosnia and Herzegovina
            'BW' => '+267', // Botswana
            'BR' => '+55', // Brazil
            'IO' => '+246', // British Indian Ocean Territory
            'VG' => '+1', // British Virgin Islands
            'BN' => '+673', // Brunei
            'BG' => '+359', // Bulgaria
            'BF' => '+226', // Burkina Faso
            'BI' => '+257', // Burundi
            'KH' => '+855', // Cambodia
            'CM' => '+237', // Cameroon
            'CA' => '+1', // Canada
            'CV' => '+238', // Cape Verde
            'KY' => '+1', // Cayman Islands
            'CF' => '+236', // Central African Republic
            'TD' => '+235', // Chad
            'CL' => '+56', // Chile
            'CN' => '+86', // China
            'CX' => '+61', // Christmas Island
            'CC' => '+61', // Cocos Islands
            'CO' => '+57', // Colombia
            'KM' => '+269', // Comoros
            'CK' => '+682', // Cook Islands
            'CR' => '+506', // Costa Rica
            'HR' => '+385', // Croatia
            'CU' => '+53', // Cuba
            'CW' => '+599', // Curacao
            'CY' => '+357', // Cyprus
            'CZ' => '+420', // Czech Republic
            'CD' => '+243', // Democratic Republic of the Congo
            'DK' => '+45', // Denmark
            'DJ' => '+253', // Djibouti
            'DM' => '+1', // Dominica
            'DO' => '+1', // Dominican Republic
            'TL' => '+670', // East Timor
            'EC' => '+593', // Ecuador
            'EG' => '+20', // Egypt
            'SV' => '+503', // El Salvador
            'GQ' => '+240', // Equatorial Guinea
            'ER' => '+291', // Eritrea
            'EE' => '+372', // Estonia
            'ET' => '+251', // Ethiopia
            'FK' => '+500', // Falkland Islands
            'FO' => '+298', // Faroe Islands
            'FJ' => '+679', // Fiji
            'FI' => '+358', // Finland
            'FR' => '+33', // France
            'PF' => '+689', // French Polynesia
            'GA' => '+241', // Gabon
            'GM' => '+220', // Gambia
            'GE' => '+995', // Georgia
            'DE' => '+49', // Germany
            'GH' => '+233', // Ghana
            'GI' => '+350', // Gibraltar
            'GR' => '+30', // Greece
            'GL' => '+299', // Greenland
            'GD' => '+1', // Grenada
            'GU' => '+1', // Guam
            'GT' => '+502', // Guatemala
            'GG' => '+44', // Guernsey
            'GN' => '+224', // Guinea
            'GW' => '+245', // Guinea-Bissau
            'GY' => '+592', // Guyana
            'HT' => '+509', // Haiti
            'HN' => '+504', // Honduras
            'HK' => '+852', // Hong Kong
            'HU' => '+36', // Hungary
            'IS' => '+354', // Iceland
            'IN' => '+91', // India
            'ID' => '+62', // Indonesia
            'IR' => '+98', // Iran
            'IQ' => '+964', // Iraq
            'IE' => '+353', // Ireland
            'IM' => '+44', // Isle of Man
            'IL' => '+972', // Israel
            'IT' => '+39', // Italy
            'CI' => '+225', // Ivory Coast
            'JM' => '+1', // Jamaica
            'JP' => '+81', // Japan
            'JE' => '+44', // Jersey
            'JO' => '+962', // Jordan
            'KZ' => '+7', // Kazakhstan
            'KE' => '+254', // Kenya
            'KI' => '+686', // Kiribati
            'XK' => '+383', // Kosovo
            'KW' => '+965', // Kuwait
            'KG' => '+996', // Kyrgyzstan
            'LA' => '+856', // Laos
            'LV' => '+371', // Latvia
            'LB' => '+961', // Lebanon
            'LS' => '+266', // Lesotho
            'LR' => '+231', // Liberia
            'LY' => '+218', // Libya
            'LI' => '+423', // Liechtenstein
            'LT' => '+370', // Lithuania
            'LU' => '+352', // Luxembourg
            'MO' => '+853', // Macau
            'MK' => '+389', // Macedonia
            'MG' => '+261', // Madagascar
            'MW' => '+265', // Malawi
            'MY' => '+60', // Malaysia
            'MV' => '+960', // Maldives
            'ML' => '+223', // Mali
            'MT' => '+356', // Malta
            'MH' => '+692', // Marshall Islands
            'MR' => '+222', // Mauritania
            'MU' => '+230', // Mauritius
            'YT' => '+262', // Mayotte
            'MX' => '+52', // Mexico
            'FM' => '+691', // Micronesia
            'MD' => '+373', // Moldova
            'MC' => '+377', // Monaco
            'MN' => '+976', // Mongolia
            'ME' => '+382', // Montenegro
            'MS' => '+1', // Montserrat
            'MA' => '+212', // Morocco
            'MZ' => '+258', // Mozambique
            'MM' => '+95', // Myanmar
            'NA' => '+264', // Namibia
            'NR' => '+674', // Nauru
            'NP' => '+977', // Nepal
            'NL' => '+31', // Netherlands
            'AN' => '+599', // Netherlands Antilles
            'NC' => '+687', // New Caledonia
            'NZ' => '+64', // New Zealand
            'NI' => '+505', // Nicaragua
            'NE' => '+227', // Niger
            'NG' => '+234', // Nigeria
            'NU' => '+683', // Niue
            'NF' => '+672', // Norfolk Island
            'KP' => '+850', // North Korea
            'MP' => '+1', // Northern Mariana Islands
            'NO' => '+47', // Norway
            'OM' => '+968', // Oman
            'PK' => '+92', // Pakistan
            'PW' => '+680', // Palau
            'PS' => '+970', // Palestinian Territory
            'PA' => '+507', // Panama
            'PG' => '+675', // Papua New Guinea
            'PY' => '+595', // Paraguay
            'PE' => '+51', // Peru
            'PH' => '+63', // Philippines
            'PN' => '+64', // Pitcairn
            'PL' => '+48', // Poland
            'PT' => '+351', // Portugal
            'PR' => '+1', // Puerto Rico
            'QA' => '+974', // Qatar
            'CG' => '+242', // Republic of the Congo
            'RE' => '+262', // Reunion
            'RO' => '+40', // Romania
            'RU' => '+7', // Russia
            'RW' => '+250', // Rwanda
            'BL' => '+590', // Saint Barthelemy
            'SH' => '+290', // Saint Helena
            'KN' => '+1', // Saint Kitts and Nevis
            'LC' => '+1', // Saint Lucia
            'MF' => '+590', // Saint Martin
            'PM' => '+508', // Saint Pierre and Miquelon
            'VC' => '+1', // Saint Vincent and the Grenadines
            'WS' => '+685', // Samoa
            'SM' => '+378', // San Marino
            'ST' => '+239', // Sao Tome and Principe
            'SA' => '+966', // Saudi Arabia
            'SN' => '+221', // Senegal
            'RS' => '+381', // Serbia
            'SC' => '+248', // Seychelles
            'SL' => '+232', // Sierra Leone
            'SG' => '+65', // Singapore
            'SX' => '+1', // Sint Maarten
            'SK' => '+421', // Slovakia
            'SI' => '+386', // Slovenia
            'SB' => '+677', // Solomon Islands
            'SO' => '+252', // Somalia
            'ZA' => '+27', // South Africa
            'KR' => '+82', // South Korea
            'SS' => '+211', // South Sudan
            'ES' => '+34', // Spain
            'LK' => '+94', // Sri Lanka
            'SD' => '+249', // Sudan
            'SR' => '+597', // Suriname
            'SJ' => '+47', // Svalbard and Jan Mayen
            'SZ' => '+268', // Swaziland
            'SE' => '+46', // Sweden
            'CH' => '+41', // Switzerland
            'SY' => '+963', // Syria
            'TW' => '+886', // Taiwan
            'TJ' => '+992', // Tajikistan
            'TZ' => '+255', // Tanzania
            'TH' => '+66', // Thailand
            'TG' => '+228', // Togo
            'TK' => '+690', // Tokelau
            'TO' => '+676', // Tonga
            'TT' => '+1', // Trinidad and Tobago
            'TN' => '+216', // Tunisia
            'TR' => '+90', // Turkey
            'TM' => '+993', // Turkmenistan
            'TC' => '+1', // Turks and Caicos Islands
            'TV' => '+688', // Tuvalu
            'VI' => '+1', // U.S. Virgin Islands
            'UG' => '+256', // Uganda
            'UA' => '+380', // Ukraine
            'AE' => '+971', // United Arab Emirates
            'GB' => '+44', // United Kingdom
            'US' => '+1', // United States
            'UY' => '+598', // Uruguay
            'UZ' => '+998', // Uzbekistan
            'VU' => '+678', // Vanuatu
            'VA' => '+39', // Vatican
            'VE' => '+58', // Venezuela
            'VN' => '+84', // Vietnam
            'WF' => '+681', // Wallis and Futuna
            'EH' => '+212', // Western Sahara
            'YE' => '+967', // Yemen
            'ZM' => '+260', // Zambia
            'ZW' => '+263', // Zimbabwe
        ];

        if (isset($countryCodes[$countryCode])) {
            return $countryCodes[$countryCode];
        } else {
            return null;
        }
    }
}

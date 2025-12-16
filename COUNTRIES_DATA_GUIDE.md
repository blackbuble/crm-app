# Countries Data - Complete World Coverage

## Overview
Sistem CRM sekarang mendukung **195 negara** di seluruh dunia dengan data lengkap termasuk:
- Nama negara
- Kode ISO (2 huruf)
- Kode telepon internasional (dial code)

## 📊 Data Source

### File: `app/Helpers/countries.json`
File JSON berisi data lengkap 195 negara dalam format:

```json
{
  "countries": [
    {
      "name": "Indonesia",
      "code": "ID",
      "dial_code": "+62"
    },
    {
      "name": "Singapore",
      "code": "SG",
      "dial_code": "+65"
    },
    ...
  ]
}
```

### Total Countries: **195**

Mencakup semua negara yang diakui PBB plus beberapa wilayah khusus seperti:
- Hong Kong
- Macau
- Taiwan
- Palestine
- Kosovo
- Vatican City

## 🔧 Helper Functions

### 1. `load_countries_data()`
Load data negara dari JSON file (dengan caching).

```php
$countries = load_countries_data();
// Returns: Array of all countries data
```

### 2. `get_countries()`
Mendapatkan daftar nama negara untuk dropdown/select.

```php
$countries = get_countries();
// Returns: ['Afghanistan' => 'Afghanistan', 'Albania' => 'Albania', ...]
// Total: 195 countries, sorted alphabetically
```

### 3. `get_country_codes()`
Mendapatkan daftar kode telepon negara untuk dropdown/select.

```php
$codes = get_country_codes();
// Returns: ['+1' => 'United States (+1)', '+62' => 'Indonesia (+62)', ...]
// Total: 195 dial codes, sorted by code
```

### 4. `get_country_by_name($name)`
Mendapatkan data lengkap negara berdasarkan nama.

```php
$country = get_country_by_name('Indonesia');
// Returns: ['name' => 'Indonesia', 'code' => 'ID', 'dial_code' => '+62']
```

### 5. `get_dial_code_by_country($countryName)`
Mendapatkan kode telepon berdasarkan nama negara.

```php
$dialCode = get_dial_code_by_country('Indonesia');
// Returns: '+62'

$dialCode = get_dial_code_by_country('Singapore');
// Returns: '+65'
```

### 6. `get_country_code_map()`
Mendapatkan mapping lengkap nama negara ke dial code.

```php
$map = get_country_code_map();
// Returns: ['Indonesia' => '+62', 'Singapore' => '+65', ...]
```

### 7. `format_whatsapp_number($phone, $countryCode)`
Format nomor telepon untuk WhatsApp.

```php
$formatted = format_whatsapp_number('8123456789', '+62');
// Returns: '628123456789'
```

### 8. `get_whatsapp_url($phone, $countryCode, $message)`
Generate URL WhatsApp lengkap.

```php
$url = get_whatsapp_url('8123456789', '+62', 'Hello!');
// Returns: 'https://wa.me/628123456789?text=Hello%21'
```

## 🌍 Daftar Negara (195 Total)

### Asia (48 negara)
Afghanistan, Armenia, Azerbaijan, Bahrain, Bangladesh, Bhutan, Brunei, Cambodia, China, Cyprus, Georgia, Hong Kong, India, Indonesia, Iran, Iraq, Israel, Japan, Jordan, Kazakhstan, Kuwait, Kyrgyzstan, Laos, Lebanon, Macau, Malaysia, Maldives, Mongolia, Myanmar, Nepal, North Korea, Oman, Pakistan, Palestine, Philippines, Qatar, Russia, Saudi Arabia, Singapore, South Korea, Sri Lanka, Syria, Taiwan, Tajikistan, Thailand, Timor-Leste, Turkey, Turkmenistan, United Arab Emirates, Uzbekistan, Vietnam, Yemen

### Europe (44 negara)
Albania, Andorra, Austria, Belarus, Belgium, Bosnia and Herzegovina, Bulgaria, Croatia, Cyprus, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Hungary, Iceland, Ireland, Italy, Kosovo, Latvia, Liechtenstein, Lithuania, Luxembourg, Malta, Moldova, Monaco, Montenegro, Netherlands, North Macedonia, Norway, Poland, Portugal, Romania, Russia, San Marino, Serbia, Slovakia, Slovenia, Spain, Sweden, Switzerland, Ukraine, United Kingdom, Vatican City

### Africa (54 negara)
Algeria, Angola, Benin, Botswana, Burkina Faso, Burundi, Cameroon, Cape Verde, Central African Republic, Chad, Comoros, Congo, Congo (DRC), Djibouti, Egypt, Equatorial Guinea, Eritrea, Eswatini, Ethiopia, Gabon, Gambia, Ghana, Guinea, Guinea-Bissau, Ivory Coast, Kenya, Lesotho, Liberia, Libya, Madagascar, Malawi, Mali, Mauritania, Mauritius, Morocco, Mozambique, Namibia, Niger, Nigeria, Rwanda, Sao Tome and Principe, Senegal, Seychelles, Sierra Leone, Somalia, South Africa, South Sudan, Sudan, Tanzania, Togo, Tunisia, Uganda, Zambia, Zimbabwe

### Americas (35 negara)
Antigua and Barbuda, Argentina, Bahamas, Barbados, Belize, Bolivia, Brazil, Canada, Chile, Colombia, Costa Rica, Cuba, Dominica, Dominican Republic, Ecuador, El Salvador, Grenada, Guatemala, Guyana, Haiti, Honduras, Jamaica, Mexico, Nicaragua, Panama, Paraguay, Peru, Saint Kitts and Nevis, Saint Lucia, Saint Vincent and the Grenadines, Suriname, Trinidad and Tobago, United States, Uruguay, Venezuela

### Oceania (14 negara)
Australia, Fiji, Kiribati, Marshall Islands, Micronesia, Nauru, New Zealand, Palau, Papua New Guinea, Samoa, Solomon Islands, Tonga, Tuvalu, Vanuatu

## 📱 Dial Codes Coverage

### Popular Dial Codes:
- **+1**: United States, Canada (dan beberapa negara Karibia)
- **+7**: Russia, Kazakhstan
- **+20**: Egypt
- **+27**: South Africa
- **+30**: Greece
- **+31**: Netherlands
- **+32**: Belgium
- **+33**: France
- **+34**: Spain
- **+39**: Italy
- **+41**: Switzerland
- **+44**: United Kingdom
- **+45**: Denmark
- **+46**: Sweden
- **+47**: Norway
- **+48**: Poland
- **+49**: Germany
- **+51**: Peru
- **+52**: Mexico
- **+53**: Cuba
- **+54**: Argentina
- **+55**: Brazil
- **+56**: Chile
- **+57**: Colombia
- **+58**: Venezuela
- **+60**: Malaysia
- **+61**: Australia
- **+62**: Indonesia
- **+63**: Philippines
- **+65**: Singapore
- **+66**: Thailand
- **+81**: Japan
- **+82**: South Korea
- **+84**: Vietnam
- **+86**: China
- **+90**: Turkey
- **+91**: India
- **+92**: Pakistan
- **+93**: Afghanistan
- **+94**: Sri Lanka
- **+95**: Myanmar
- **+98**: Iran
- **+212**: Morocco
- **+213**: Algeria
- **+216**: Tunisia
- **+218**: Libya
- **+220**: Gambia
- **+221**: Senegal
- **+222**: Mauritania
- **+223**: Mali
- **+224**: Guinea
- **+225**: Ivory Coast
- **+226**: Burkina Faso
- **+227**: Niger
- **+228**: Togo
- **+229**: Benin
- **+230**: Mauritius
- **+231**: Liberia
- **+232**: Sierra Leone
- **+233**: Ghana
- **+234**: Nigeria
- **+235**: Chad
- **+236**: Central African Republic
- **+237**: Cameroon
- **+238**: Cape Verde
- **+239**: Sao Tome and Principe
- **+240**: Equatorial Guinea
- **+241**: Gabon
- **+242**: Congo
- **+243**: Congo (DRC)
- **+244**: Angola
- **+245**: Guinea-Bissau
- **+248**: Seychelles
- **+249**: Sudan
- **+250**: Rwanda
- **+251**: Ethiopia
- **+252**: Somalia
- **+253**: Djibouti
- **+254**: Kenya
- **+255**: Tanzania
- **+256**: Uganda
- **+257**: Burundi
- **+258**: Mozambique
- **+260**: Zambia
- **+261**: Madagascar
- **+263**: Zimbabwe
- **+264**: Namibia
- **+265**: Malawi
- **+266**: Lesotho
- **+267**: Botswana
- **+268**: Eswatini
- **+269**: Comoros
- **+350**: Gibraltar
- **+351**: Portugal
- **+352**: Luxembourg
- **+353**: Ireland
- **+354**: Iceland
- **+355**: Albania
- **+356**: Malta
- **+357**: Cyprus
- **+358**: Finland
- **+359**: Bulgaria
- **+370**: Lithuania
- **+371**: Latvia
- **+372**: Estonia
- **+373**: Moldova
- **+374**: Armenia
- **+375**: Belarus
- **+376**: Andorra
- **+377**: Monaco
- **+378**: San Marino
- **+379**: Vatican City
- **+380**: Ukraine
- **+381**: Serbia
- **+382**: Montenegro
- **+383**: Kosovo
- **+385**: Croatia
- **+386**: Slovenia
- **+387**: Bosnia and Herzegovina
- **+389**: North Macedonia
- **+420**: Czech Republic
- **+421**: Slovakia
- **+423**: Liechtenstein
- **+501**: Belize
- **+502**: Guatemala
- **+503**: El Salvador
- **+504**: Honduras
- **+505**: Nicaragua
- **+506**: Costa Rica
- **+507**: Panama
- **+509**: Haiti
- **+590**: Guadeloupe
- **+591**: Bolivia
- **+592**: Guyana
- **+593**: Ecuador
- **+595**: Paraguay
- **+597**: Suriname
- **+598**: Uruguay
- **+670**: Timor-Leste
- **+673**: Brunei
- **+674**: Nauru
- **+675**: Papua New Guinea
- **+676**: Tonga
- **+677**: Solomon Islands
- **+678**: Vanuatu
- **+679**: Fiji
- **+680**: Palau
- **+685**: Samoa
- **+686**: Kiribati
- **+688**: Tuvalu
- **+691**: Micronesia
- **+692**: Marshall Islands
- **+850**: North Korea
- **+852**: Hong Kong
- **+853**: Macau
- **+855**: Cambodia
- **+856**: Laos
- **+880**: Bangladesh
- **+886**: Taiwan
- **+960**: Maldives
- **+961**: Lebanon
- **+962**: Jordan
- **+963**: Syria
- **+964**: Iraq
- **+965**: Kuwait
- **+966**: Saudi Arabia
- **+967**: Yemen
- **+968**: Oman
- **+970**: Palestine
- **+971**: United Arab Emirates
- **+972**: Israel
- **+973**: Bahrain
- **+974**: Qatar
- **+975**: Bhutan
- **+976**: Mongolia
- **+977**: Nepal
- **+992**: Tajikistan
- **+993**: Turkmenistan
- **+994**: Azerbaijan
- **+995**: Georgia
- **+996**: Kyrgyzstan
- **+998**: Uzbekistan
- **+1242**: Bahamas
- **+1246**: Barbados
- **+1268**: Antigua and Barbuda
- **+1473**: Grenada
- **+1758**: Saint Lucia
- **+1767**: Dominica
- **+1784**: Saint Vincent and the Grenadines
- **+1809**: Dominican Republic
- **+1868**: Trinidad and Tobago
- **+1869**: Saint Kitts and Nevis
- **+1876**: Jamaica

## 💡 Usage Examples

### Example 1: Customer Form
```php
// Country dropdown - shows all 195 countries
Forms\Components\Select::make('country')
    ->options(get_countries())
    ->searchable()
    ->live()
    ->afterStateUpdated(function ($state, $set) {
        // Auto-fill dial code for ANY country
        $dialCode = get_dial_code_by_country($state);
        if ($dialCode) {
            $set('country_code', $dialCode);
        }
    })
```

### Example 2: WhatsApp Integration
```php
// Works with ANY country
$url = get_whatsapp_url('123456789', '+44', 'Hello from UK!');
// Returns: https://wa.me/44123456789?text=Hello%20from%20UK%21
```

### Example 3: Get Country Info
```php
$country = get_country_by_name('Japan');
// Returns: ['name' => 'Japan', 'code' => 'JP', 'dial_code' => '+81']
```

## 🎯 Benefits

1. **Complete Coverage**: Semua 195 negara di dunia
2. **Easy Maintenance**: Data terpusat di JSON file
3. **Auto-Complete**: Dial code otomatis terisi saat pilih negara
4. **Searchable**: Semua dropdown bisa di-search
5. **Alphabetically Sorted**: Negara diurutkan alfabetis
6. **Performance**: Data di-cache untuk performa optimal
7. **Flexible**: Mudah ditambah/edit negara baru

## 📝 Notes

- Data negara menggunakan ISO 3166-1 alpha-2 codes
- Dial codes sesuai ITU-T E.164 standard
- JSON file bisa di-update kapan saja tanpa ubah code
- Helper functions otomatis reload data jika JSON berubah

---

**Total Countries**: 195  
**Total Dial Codes**: 195+ (beberapa negara share dial code seperti +1)  
**Last Updated**: 2025-12-12  
**Data Source**: ISO 3166-1 & ITU-T E.164

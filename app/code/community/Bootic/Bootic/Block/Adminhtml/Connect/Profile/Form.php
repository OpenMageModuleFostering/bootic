<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Connect_Profile_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_countries = array(
        "" => "-- Please Select --",
        "11" => "Afghanistan",
        "12" => "Albania",
        "13" => "Algeria",
        "14" => "Andorra",
        "15" => "Angola",
        "16" => "Antigua and Barbuda",
        "17" => "Argentina",
        "18" => "Armenia",
        "19" => "Australia",
        "20" => "Austria",
        "21" => "Azerbaijan",
        "22" => "Bahamas, The",
        "23" => "Bahrain",
        "24" => "Bangladesh",
        "25" => "Barbados",
        "26" => "Belarus",
        "27" => "Belgium",
        "28" => "Belize",
        "29" => "Benin",
        "30" => "Bhutan",
        "31" => "Bolivia",
        "32" => "Bosnia and Herzegovina",
        "33" => "Botswana",
        "34" => "Brazil",
        "35" => "Brunei",
        "36" => "Bulgaria",
        "37" => "Burkina Faso",
        "38" => "Burma",
        "39" => "Burundi",
        "40" => "Cambodia",
        "41" => "Cameroon",
        "10" => "Canada",
        "42" => "Cape Verde",
        "43" => "Central African Republic",
        "44" => "Chad",
        "45" => "Chile",
        "5" => "China",
        "46" => "Colombia",
        "47" => "Comoros",
        "48" => "Congo, Democratic Republic of the",
        "49" => "Congo, Republic of the",
        "50" => "Costa Rica",
        "51" => "Cote d Ivoire",
        "52" => "Croatia",
        "53" => "Cuba",
        "54" => "Cyprus",
        "55" => "Czech Republic",
        "56" => "Denmark",
        "57" => "Djibouti",
        "58" => "Dominica",
        "59" => "Dominican Republic",
        "61" => "Ecuador",
        "62" => "Egypt",
        "63" => "El Salvador",
        "64" => "Equatorial Guinea",
        "65" => "Eritrea",
        "66" => "Estonia",
        "67" => "Ethiopia",
        "68" => "Fiji",
        "69" => "Finland",
        "1" => "France",
        "70" => "Gabon",
        "71" => "Gambia, The",
        "72" => "Georgia",
        "73" => "Germany",
        "74" => "Ghana",
        "75" => "Greece",
        "76" => "Grenada",
        "77" => "Guatemala",
        "78" => "Guinea",
        "79" => "Guinea-Bissau",
        "80" => "Guyana",
        "81" => "Haiti",
        "82" => "Holy See",
        "83" => "Honduras",
        "84" => "Hong Kong",
        "85" => "Hungary",
        "86" => "Iceland",
        "87" => "India",
        "88" => "Indonesia",
        "89" => "Iran",
        "90" => "Iraq",
        "7" => "Ireland",
        "91" => "Israel",
        "92" => "Italy",
        "93" => "Jamaica",
        "94" => "Japan",
        "95" => "Jordan",
        "96" => "Kazakhstan",
        "97" => "Kenya",
        "98" => "Kiribati",
        "99" => "Korea, North",
        "100" => "Korea, South",
        "101" => "Kosovo",
        "102" => "Kuwait",
        "103" => "Kyrgyzstan",
        "104" => "Laos",
        "105" => "Latvia",
        "106" => "Lebanon",
        "107" => "Lesotho",
        "108" => "Liberia",
        "109" => "Libya",
        "110" => "Liechtenstein",
        "111" => "Lithuania",
        "112" => "Luxembourg",
        "113" => "Macau",
        "114" => "Macedonia",
        "115" => "Madagascar",
        "116" => "Malawi",
        "117" => "Malaysia",
        "118" => "Maldives",
        "119" => "Mali",
        "120" => "Malta",
        "121" => "Marshall Islands",
        "122" => "Mauritania",
        "123" => "Mauritius",
        "124" => "Mexico",
        "125" => "Micronesia",
        "126" => "Moldova",
        "127" => "Monaco",
        "128" => "Mongolia",
        "129" => "Montenegro",
        "130" => "Morocco",
        "131" => "Mozambique",
        "132" => "Namibia",
        "133" => "Nauru",
        "134" => "Nepal",
        "135" => "Netherlands",
        "136" => "Netherlands Antilles",
        "137" => "New Zealand",
        "138" => "Nicaragua",
        "139" => "Niger",
        "140" => "Nigeria",
        "141" => "North Korea",
        "142" => "Norway",
        "143" => "Oman",
        "144" => "Pakistan",
        "145" => "Palau",
        "146" => "Palestinian Territories",
        "147" => "Panama",
        "148" => "Papua New Guinea",
        "149" => "Paraguay",
        "150" => "Peru",
        "151" => "Philippines",
        "152" => "Poland",
        "153" => "Portugal",
        "154" => "Qatar",
        "155" => "Romania",
        "8" => "Russia",
        "156" => "Rwanda",
        "157" => "Saint Kitts and Nevis",
        "158" => "Saint Lucia",
        "159" => "Saint Vincent and the Grenadines",
        "160" => "Samoa ",
        "161" => "San Marino",
        "162" => "Sao Tome and Principe",
        "163" => "Saudi Arabia",
        "164" => "Senegal",
        "165" => "Serbia",
        "166" => "Seychelles",
        "167" => "Sierra Leone",
        "6" => "Singapore",
        "168" => "Slovakia",
        "169" => "Slovenia",
        "170" => "Solomon Islands",
        "171" => "Somalia",
        "172" => "South Africa",
        "173" => "South Korea",
        "174" => "South Sudan",
        "9" => "Spain",
        "175" => "Sri Lanka",
        "176" => "Sudan",
        "177" => "Suriname",
        "178" => "Swaziland ",
        "179" => "Sweden",
        "180" => "Switzerland",
        "181" => "Syria",
        "182" => "Taiwan",
        "183" => "Tajikistan",
        "184" => "Tanzania",
        "185" => "Thailand ",
        "60" => "Timor-Leste",
        "187" => "Togo",
        "188" => "Tonga",
        "189" => "Trinidad and Tobago",
        "190" => "Tunisia",
        "191" => "Turkey",
        "192" => "Turkmenistan",
        "193" => "Tuvalu",
        "194" => "Uganda",
        "195" => "Ukraine",
        "196" => "United Arab Emirates",
        "4" => "United Kingdom",
        "197" => "Uruguay",
        "2" => "USA",
        "198" => "Uzbekistan",
        "199" => "Vanuatu",
        "200" => "Venezuela",
        "201" => "Vietnam",
        "202" => "Yemen",
        "203" => "Zambia",
        "204" => "Zimbabwe"
    );

    protected $_regions = array(
        "" => "-- Please select --",
        "1" => "Alabama",
        "2" => "Alaska",
        "3" => "Arizona",
        "4" => "Arkansas",
        "5" => "California",
        "6" => "Colorado",
        "7" => "Connecticut",
        "8" => "Delaware",
        "51" => "District of Columbia",
        "9" => "Florida",
        "10" => "Georgia",
        "11" => "Hawaii",
        "12" => "Idaho",
        "13" => "Illinois",
        "14" => "Indiana",
        "15" => "Iowa",
        "16" => "Kansas",
        "17" => "Kentucky",
        "18" => "Louisiana",
        "19" => "Maine",
        "20" => "Maryland",
        "21" => "Massachusetts",
        "22" => "Michigan",
        "23" => "Minnesota",
        "24" => "Mississippi",
        "25" => "Missouri",
        "26" => "Montana",
        "27" => "Nebraska",
        "28" => "Nevada",
        "29" => "New Hampshire",
        "30" => "New Jersey",
        "31" => "New Mexico",
        "32" => "New York",
        "33" => "North Carolina",
        "34" => "North Dakota",
        "35" => "Ohio",
        "36" => "Oklahoma",
        "37" => "Oregon",
        "38" => "Pennsylvania",
        "39" => "Rhode Island",
        "40" => "South Carolina",
        "41" => "South Dakota",
        "42" => "Tennessee",
        "43" => "Texas",
        "44" => "Utah",
        "45" => "Vermont",
        "46" => "Virginia",
        "47" => "Washington",
        "48" => "West Virginia",
        "49" => "Wisconsin",
        "50" => "Wyoming",
        "0" => "Not applicable"
    );


    protected function _prepareForm()
    {
        $helper = Mage::helper('bootic');

        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/save'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('connect_profile_form', array('legend' => $helper->__('Profile')));

        $fieldset->addField('name', 'text', array(
            'label' => $helper->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('company', 'text', array(
            'label' => $helper->__('Company'),
            'name' => 'company',
        ));

        $fieldset->addField('address', 'text', array(
            'label' => $helper->__('Address line 1'),
            'name' => 'address'
        ));

        $fieldset->addField('address2', 'text', array(
            'label' => $helper->__('Address line 2'),
            'name' => 'address2'
        ));

        $countries = array();
        foreach ($this->_countries as $code => $country) {
            $countries[] = array(
                'value' => $code,
                'label' => $helper->__($country)
            );
        }

        $fieldset->addField('country', 'select', array(
            'label' => $helper->__('Country'),
            'name' => 'country',
            'class' => 'required-entry',
            'required' => true,
            'values' => $countries
        ));

        $regions = array();
        foreach ($this->_regions as $code => $region) {
            $regions[] = array(
                'value' => $code,
                'label' => $helper->__($region)
            );
        }

        $fieldset->addField('region', 'select', array(
            'label' => $helper->__('State'),
            'name' => 'region',
            'class' => 'required-entry',
            'required' => true,
            'values' => $regions,
        ));

        $fieldset->addField('city', 'text', array(
            'label' => $helper->__('City'),
            'name' => 'city',
        ));

        $fieldset->addField('post_code', 'text', array(
            'label' => $helper->__('Postal Code'),
            'name' => 'post_code'
        ));

        $fieldset->addField('phone_number', 'text', array(
            'label' => $helper->__('Phone'),
            'name' => 'phone_number'
        ));

        $fieldset->addField('ssn_tax_id', 'text', array(
            'label' => $helper->__('Tax ID (or SSN for individuals)'),
            'name' => 'ssn_tax_id'
        ));

        $fieldset->addField('show_phone_number', 'select', array(
            'label' => Mage::helper('bootic')->__('Display my phone number on storefront:'),
            'name' => 'show_phone_number',
            'values' => array(
                0 => array(
                    'value' => 1,
                    'label' => 'Yes'
                ),
                1 => array(
                    'value' => 0,
                    'label' => 'No'
                )
            )
        ));

        $fieldset2 = $form->addFieldset('connect_profile_image_form', array('legend' => $helper->__('Profile Image')));

        $fieldset2->addField('picture', 'image', array(
            'label' => $helper->__('Profile Image'),
            'name' => 'picture'
        ));

        $fieldset3 = $form->addFieldset('connect_profile_paypal_form', array('legend' => $helper->__('Paypal Account')));

        $fieldset3->addField('payment_merchant_paypal_account', 'text', array(
            'label' => $helper->__('Paypal Account for Bootic withdrawal:'),
            'name' => 'payment_merchant_paypal_account'
        ));

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap('country', 'country')
                ->addFieldMap('region', 'region')
                ->addFieldDependence('region', 'country', '2')
        );

        $form->setValues(Mage::getSingleton('bootic/profile')->getData());

        return parent::_prepareForm();
    }
}

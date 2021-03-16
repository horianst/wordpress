<?php
/*
Plugin Name: Cargus
Plugin URI: https://www.cargus.ro
Description: Metoda de livrare Cargus pentru WooCommerce
Version: 1.1
License: GPL2
*/

if (!defined('WPINC')) {
    die;
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function urgentcargus_shipping_method() {
        if (!class_exists('UrgentCargus_Shipping_Method')) {

            require_once(plugin_dir_path(__FILE__) . 'urgentcargus.class.php');

            class UrgentCargus_Shipping_Method extends WC_Shipping_Method
            {
                public $uc;

                public function __construct() {
                    $this->id                 = 'urgentcargus';
                    $this->method_title       = __('Livrare cu Cargus', 'urgentcargus');

//                    $this->availability = 'including';
//                    $this->countries = array(
//                        'RO' // Romania
//                    );

                    $this->init();

                    $this->title = isset($this->settings['title']) ? $this->settings['title'] : null;
                    $this->webservice = isset($this->settings['webservice']) ? $this->settings['webservice'] : null;
                    $this->apikey = isset($this->settings['apikey']) ? $this->settings['apikey'] : null;
                    $this->username = isset($this->settings['username']) ? $this->settings['username'] : null;
                    $this->password = isset($this->settings['password']) ? $this->settings['password'] : null;
                    $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'no';

                    $this->pickup = isset($this->settings['pickup']) ? $this->settings['pickup'] : null;
                    $this->priceplan = isset($this->settings['priceplan']) ? $this->settings['priceplan'] : null;
                    $this->insurance = isset($this->settings['insurance']) ? $this->settings['insurance'] : null;
                    $this->saturday = isset($this->settings['saturday']) ? $this->settings['saturday'] : null;
                    $this->morning = isset($this->settings['morning']) ? $this->settings['morning'] : null;
                    $this->open = isset($this->settings['open']) ? $this->settings['open'] : null;
                    $this->repayment = isset($this->settings['repayment']) ? $this->settings['repayment'] : null;
                    $this->payer = isset($this->settings['payer']) ? $this->settings['payer'] : null;
                    $this->type = isset($this->settings['type']) ? $this->settings['type'] : null;
                    $this->free = isset($this->settings['free']) ? $this->settings['free'] : null;
                    $this->fixed = isset($this->settings['fixed']) ? $this->settings['fixed'] : null;
                    $this->height = isset($this->settings['height']) ? $this->settings['height'] : null;
                    $this->width = isset($this->settings['width']) ? $this->settings['width'] : null;
                    $this->length = isset($this->settings['length']) ? $this->settings['length'] : null;

                    $this->uc = new UrgentCargusClass();
                    if (!empty($this->webservice) && !empty($this->apikey)) {
                        $this->uc->SetKeys($this->webservice, $this->apikey);
                        $fields = array(
                            'UserName' => $this->username,
                            'Password' => $this->password
                        );
                        $this->token = $this->uc->CallMethod('LoginUser', $fields, 'POST');

                        if ($this->token !== 'error') {
                            $this->init_extra_fields();
                        }
                    }
                }

                function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                function init_form_fields() {
                    $this->form_fields = array(
                        'title' => array(
                            'title'         => __('Titlu', 'urgentcargus'),
                            'type'          => 'text',
                            'default'       => __('Livrare cu Cargus', 'urgentcargus')
                        ),
                        'webservice' => array(
                            'title'         => __('URL Webservice', 'urgentcargus'),
                            'type'          => 'text',
                            'default'       => __('https://urgentcargus.azure-api.net/api', 'urgentcargus')
                        ),
                        'apikey' => array(
                            'title'         => __('API Key', 'urgentcargus'),
                            'type'          => 'text',
                        ),
                        'username' => array(
                            'title'         => __('Nume utilizator', 'urgentcargus'),
                            'type'          => 'text',
                        ),
                        'password' => array(
                            'title'         => __('Parola', 'urgentcargus'),
                            'type'          => 'password',
                        ),
                        'enabled' => array(
                            'title'         => __('Status', 'urgentcargus'),
                            'label'         => __('Activ', 'urgentcargus'),
                            'type'          => 'checkbox',
                            'default'       => 'yes'
                        ),
                    );
                }

                function init_extra_fields() {
                    // obtine lista punctelor de ridicare
                    $temp = $this->uc->CallMethod('PickupLocations', array(), 'GET', $this->token);
                    $pickups = array();
                    if (is_array($temp)) {
                        foreach ($temp as $t) {
                            $pickups[$t['LocationId']] = $t['Name'];
                        }
                    }

                    // obtine lista planurilor tarifare
                    $temp = $this->uc->CallMethod('PriceTables', array(), 'GET', $this->token);

                    $prices = array();
                    if (is_array($temp)) {
                        foreach ($temp as $t) {
                            $prices[$t['PriceTableId']] = empty($t['Name']) ? $t['PriceTableId'] : $t['Name'];
                        }
                    }

                    $this->form_fields += array(
                        'pickup' => array(
                            'title'         => __('Punct de ridicare', 'urgentcargus'),
                            'type'          => 'select',
                            'class'         => 'select_height',
                            'options'       => array(null => 'Alege punctul de ridicare') + $pickups
                        ),
                        'priceplan' => array(
                            'title'         => __('Plan tarifar', 'urgentcargus'),
                            'type'          => 'select',
                            'class'         => 'select_height',
                            'options'       => array(null => 'Alege planul tarifar') + $prices
                        ),
                        'insurance' => array(
                            'title'         => __('', 'urgentcargus'),
                            'label'         => __('Asigurare', 'urgentcargus'),
                            'type'          => 'checkbox',
                            'default'       => 'no'
                        ),
                        'saturday' => array(
                            'title'         => __('', 'urgentcargus'),
                            'label'         => __('Livrare sambata', 'urgentcargus'),
                            'type'          => 'checkbox',
                            'default'       => 'no'
                        ),
                        'morning' => array(
                            'title'         => __('', 'urgentcargus'),
                            'label'         => __('Livrare dimineata', 'urgentcargus'),
                            'type'          => 'checkbox',
                            'default'       => 'no'
                        ),
                        'open' => array(
                            'title'         => __('', 'urgentcargus'),
                            'label'         => __('Deschidere colet', 'urgentcargus'),
                            'type'          => 'checkbox',
                            'default'       => 'no'
                        ),
                        'repayment' => array(
                            'title'         => __('Incasare ramburs', 'urgentcargus'),
                            'type'          => 'select',
                            'class'         => 'select_height',
                            'options'       => array(
                                'cash' => 'Numerar',
                                'bank' => 'Transfer bancar'
                            )
                        ),
                        'payer' => array(
                            'title'         => __('Platitor expeditie', 'urgentcargus'),
                            'type'          => 'select',
                            'class'         => 'select_height',
                            'options'       => array(
                                'sender' => 'Expeditor',
                                'recipient' => 'Destinatar'
                            )
                        ),
                        'type' => array(
                            'title'         => __('Tip expeditie', 'urgentcargus'),
                            'type'          => 'select',
                            'class'         => 'select_height',
                            'options'       => array(
                                'parcel' => 'Colet',
                                'envelope' => 'Plic'
                            )
                        ),
                        'free' => array(
                            'title'         => __('Plafon transport gratuit', 'urgentcargus'),
                            'type'          => 'text',
                        ),
                        'fixed' => array(
                            'title'         => __('Cost fix transport', 'urgentcargus'),
                            'type'          => 'text',
                        ),

                        'height' => array(
                            'title'         => __('Inaltime', 'urgentcargus'),
                            'type'          => 'number',
                        ),

                        'width' => array(
                            'title'         => __('Latime', 'urgentcargus'),
                            'type'          => 'number',
                        ),

                        'length' => array(
                            'title'         => __('Lungime', 'urgentcargus'),
                            'type'          => 'number',
                        ),
                    );
                }

                public function calculate_shipping($package = array()) {
                    $calculatedCost = $this->getShippingCost($package);

                    if (!is_null($calculatedCost)) {
                        if ($calculatedCost == 0) $this->title .= ' - Gratuit';

                        $rate = array(
                            'id' => $this->id,
                            'label' => $this->title,
                            'cost' => $calculatedCost
                        );

                        $this->add_rate($rate);
                    }
                }

                private function getShippingCost($package) {
                    try {
                        // Payemnt method
                        $available_payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
                        if (isset($_POST) && isset($_POST['payment_method']) && isset($available_payment_gateways[$_POST['payment_method']])) {
                            $current_payment_gateway = $available_payment_gateways[$_POST['payment_method']];
                        } elseif (isset(WC()->session->chosen_payment_method ) && isset($available_payment_gateways[WC()->session->chosen_payment_method])) {
                            $current_payment_gateway = $available_payment_gateways[WC()->session->chosen_payment_method];
                        } elseif (isset($available_payment_gateways[get_option('woocommerce_default_gateway')])) {
                            $current_payment_gateway = $available_payment_gateways[get_option('woocommerce_default_gateway')];
                        } else {
                            $current_payment_gateway = current($available_payment_gateways);
                        }

                        // UC check fixed
                        if ($this->fixed != '' && is_numeric($this->fixed)) return $this->fixed;

                        // Check free shipping coupon
                        if ($coupons = WC()->cart->get_coupons()) {
                            foreach ($coupons as $code => $coupon) {
                                if ($coupon->is_valid() && $coupon->get_free_shipping()) {
                                    return 0;
                                }
                            }
                        }

                        // Get total
                        $total = WC()->cart->cart_contents_total + array_sum(WC()->cart->taxes);

                        // Get ramburs
                        $ramburs = 0;
                        if ($current_payment_gateway->id == 'cod') {
                            $ramburs = $total;
                        }

                        // UC check free
                        if (!empty($this->free) && $total >= $this->free) {
                            return 0;
                        }

                        // Get weight
                        $weight = 0;
                        foreach ($package['contents'] as $item_id => $values) {
                            $_product = $values['data'];
                            $weight = $weight + (($_product->get_weight() == null ? 1 : $_product->get_weight()) * $values['quantity']);
                        }
                        $weight = ceil(wc_get_weight($weight, 'kg'));
                        if ($weight < 1) $weight = 1;

                        // UC punctul de ridicare default
                        $location = array();
                        $pickups = $this->uc->CallMethod('PickupLocations', array(), 'GET', $this->token);
                        if (is_null($pickups) || $pickups === 'error') return null;
                        foreach ($pickups as $pick) {
                            if ($this->pickup == $pick['LocationId']) {
                                $location = $pick;
                            }
                        }
                        if (empty($location)) return null;

                        // UC shipping calculation
                        $fields = array(
                            'FromLocalityId' => $location['LocalityId'],
                            'ToLocalityId' => 0,
                            'FromCountyName' => '',
                            'FromLocalityName' => '',
                            'ToCountyName' => trim($package['destination']['state']),
                            'ToLocalityName' => trim($package['destination']['state']) == 'B' ? 'Bucuresti' : trim($package['destination']['city']),
                            'Parcels' => $this->type == 'envelope' ? 0 : 1,
                            'Envelopes' => $this->type == 'envelope' ? 1 : 0,
                            'TotalWeight' => $weight,
                            'DeclaredValue' => $this->insurance == 'yes' ? $total : 0,
                            'CashRepayment' => $this->repayment == 'bank' ? 0 : $ramburs,
                            'BankRepayment' => $this->repayment == 'bank' ? $ramburs : 0,
                            'OtherRepayment' => '',
                            'PaymentInstrumentId' => 0,
                            'PaymentInstrumentValue' => 0,
                            'OpenPackage' => $this->open == 'yes' ? true : false,
                            'SaturdayDelivery' => $this->saturday == 'yes' ? true : false,
                            'MorningDelivery' => $this->morning == 'yes' ? true : false,
                            'ShipmentPayer' => $this->payer == 'recipient' ? 2 : 1,
                            'ServiceId' => $this->payer == 'recipient' ? 4 : 1,
                            'PriceTableId' => $this->priceplan
                        );
                        $result = $this->uc->CallMethod('ShippingCalculation', $fields, 'POST', $this->token);
                        if (is_null($result) || $result === 'error') return null;

                        return $result['Subtotal'];
                    } catch (Exception $ex) {
                        return null;
                    }
                }
            }
        }
    }

    add_action('woocommerce_shipping_init', 'urgentcargus_shipping_method');

    function add_urgentcargus_shipping_method($methods) {
        $methods[] = 'UrgentCargus_Shipping_Method';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'add_urgentcargus_shipping_method');

    function urgentcargus_scripts_method() {
        wp_enqueue_script(
            'urgentcargus',
            plugins_url() . '/urgentcargus/urgentcargus.js',
            array('jquery')
        );
    }

    add_action('wp_enqueue_scripts', 'urgentcargus_scripts_method');

    function urgentcargus_regions()
    {
        if (isset($_GET['urgentcargus']) && isset($_GET['judet']) && isset($_GET['val'])) {
            urgentcargus_shipping_method();
            $ucsm = new UrgentCargus_Shipping_Method();

            if ($ucsm->token !== 'error') {
                // obtin lista de judete din api
                $judete = array();
                $temp = $ucsm->uc->CallMethod('Counties?countryId=1', array(), 'GET', $ucsm->token);
                foreach ($temp as $t) {
                    $judete[strtolower($t['Abbreviation'])] = $t['CountyId'];
                }

                // obtin lista de localitati pe baza id-ului judetului
                $localitati = $ucsm->uc->CallMethod('Localities?countryId=1&countyId=' . $judete[trim(strtolower(addslashes($_GET['judet'])))], array(), 'GET', $ucsm->token);

                // generez options pentru dropdown
                if (count($localitati) > 1) {
                    echo '<option value="" km="0">-</option>' . "\n";
                }
                foreach ($localitati as $row) {
                    echo '<option' . (trim(strtolower(addslashes($_GET['val']))) == trim(strtolower($row['Name'])) ? ' selected="selected"' : '') . ' km="' . ($row['InNetwork'] ? 0 : (!$row['ExtraKm'] ? 0 : $row['ExtraKm'])) . '">' . $row['Name'] . '</option>' . "\n";
                }
            }

            exit();
        }
    }

    add_action('wp', 'urgentcargus_regions');

    function urgentcargus_createAwb($order_id)
    {
        if (is_admin()) {
            // obtin comanda, greutatea si comentariile
            $order = wc_get_order($order_id);

            if(!trim($order->get_shipping_postcode())){
                echo 'Va rugam sa introduceti codul postal al destinatarului';
                die();
            }

            if (!$order->has_shipping_method('urgentcargus')) {
                $order->add_order_note('no urgentcargus', 1);
                return;
            }

            $notes = $order->get_customer_order_notes();
            $products = $order->get_items();
            $contents = array();
            $weight = 0;
            foreach ($products as $p) {
                $_product = wc_get_product($p['product_id']);
                $contents[] = $p['name'];
                $weight = $weight + ($_product->get_weight() * $p['quantity']);
            }
            $weight = ceil(wc_get_weight($weight, 'kg'));
            if ($weight < 1) $weight = 1;
            $awb = '';

            // determin ramburs-ul
            $ramburs = $order->get_total();
            if ($order->get_payment_method() != 'cod') {
                $ramburs = 0;
            }

            // verific daca exista deja un awb creat
            foreach ($notes as $note) {
                if (stristr($note->comment_content, 'Expeditia Cargus')) {
                    preg_match('/#(.*?) /', $note->comment_content, $match);
                    if (!empty($match)) {
                        $awb = $match[1];
                        break;
                    }
                }
            }

            // daca nu exista niciun awb atunci adaug unul
            if (empty($awb)) {
                urgentcargus_shipping_method();
                $ucsm = new UrgentCargus_Shipping_Method();

                if(!$ucsm->length || !$ucsm->width || !$ucsm->height){
                    echo 'Va rugam sa introduceti dimensiunile coletului';
                    die();
                }


                if ($ucsm->token !== 'error') {
                    $fields = array(
                        'Sender' => array(
                            'LocationId' => $ucsm->pickup
                        ),
                        'Recipient' => array(
                            'LocationId' => null,
                            'Name' => trim($order->get_shipping_company()) != '' ? trim($order->get_shipping_company()) : trim($order->get_formatted_shipping_full_name()),
                            'CountyId' => null,
                            'CountyName' => trim($order->get_shipping_state()),
                            'LocalityId' => null,
                            'LocalityName' => trim($order->get_shipping_city()),
                            'StreetId' => null,
                            'StreetName' => '-',
                            'AddressText' => trim($order->get_shipping_address_1()),
                            'ContactPerson' => trim($order->get_formatted_shipping_full_name()),
                            'PhoneNumber' => trim($order->get_billing_phone()),
                            'Email' => trim($order->get_billing_email()),
                            'CodPostal' => trim($order->get_shipping_postcode())
                        ),
                        'Parcels' => $ucsm->type == 'envelope' ? 0 : 1,
                        'Envelopes' => $ucsm->type == 'envelope' ? 1 : 0,
                        'TotalWeight' => $weight,
                        'DeclaredValue' => $ucsm->insurance == 'yes' ? ($order->get_total() - $order->get_shipping_total()) : 0,
                        'CashRepayment' => $ucsm->repayment == 'bank' ? 0 : $ramburs,
                        'BankRepayment' => $ucsm->repayment == 'bank' ? $ramburs : 0,
                        'OtherRepayment' => '',
                        'OpenPackage' => $ucsm->open == 'yes' ? true : false,
                        'ShipmentPayer' => $ucsm->payer == 'recipient' ? 2 : 1,
                        'MorningDelivery' => $ucsm->morning == 'yes' ? true : false,
                        'SaturdayDelivery' => $ucsm->saturday == 'yes' ? true : false,
                        'Observations' => '',
                        'PackageContent' => implode(' | ', $contents),
                        'CustomString' => $order_id,
                        "ParcelCodes" => [
                            [
                                "Code"=> 0,
                                "Type"=>   $ucsm->type == 'envelope' ? 0 : 1,
                                "Weight" => $weight,
                                "Length" => $ucsm->length,
                                "Width" => $ucsm->width,
                                "Height" => $ucsm->height,
                                "ParcelContent" => implode(' | ', $contents)
                            ]
                        ]
                    );

                    $barcode = $ucsm->uc->CallMethod('Awbs', $fields, 'POST', $ucsm->token);

                    if ($barcode !== 'error') {
                        $order->add_order_note('Expeditia Cargus cu numarul #' . $barcode . ' a fost creata!', 1);
                    }
                }
            }
        }
    }

    add_action('woocommerce_order_status_processing', 'urgentcargus_createAwb');
    add_action('woocommerce_order_status_completed', 'urgentcargus_createAwb');

    function urgentcargus_deleteAwb($order_id)
    {
        // obtin comanda si comentariile
        $order = wc_get_order($order_id);
        $notes = $order->get_customer_order_notes();
        $awb = '';
        $comment_id = 0;

        // verific daca exista deja un awb creat
        foreach ($notes as $note) {
            if (stristr($note->comment_content, 'Expeditia Cargus')) {
                preg_match('/#(.*?) /', $note->comment_content, $match);
                if (!empty($match)) {
                    $awb = $match[1];
                    $comment_id = $note->comment_ID;
                    break;
                }
            }
        }

        // daca am gasit un awb atunci il sterg
        if (!empty($awb)) {
            urgentcargus_shipping_method();
            $ucsm = new UrgentCargus_Shipping_Method();

            if ($ucsm->token !== 'error') {
                // sterg awb-ul din api urgent cargus
                $result = $ucsm->uc->CallMethod('Awbs?barCode=' . addslashes($awb), array(), 'DELETE', $ucsm->token);

                // sterg comentariul cu numarul awb-ului
                if ($result == 1 && !empty($comment_id)) {
                    wp_delete_comment($comment_id, 1);
                }
            }
        }
    }

    add_action('woocommerce_order_status_on-hold', 'urgentcargus_deleteAwb');
    add_action('woocommerce_order_status_cancelled', 'urgentcargus_deleteAwb');
    add_action('woocommerce_order_status_refunded', 'urgentcargus_deleteAwb');
    add_action('woocommerce_order_status_failed', 'urgentcargus_deleteAwb');
}
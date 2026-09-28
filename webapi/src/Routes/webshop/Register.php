<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->post('/webshop/register', function (Request $request, Response $response) {
    $posted_data = $request->getParsedBody();
    extract($posted_data);
    $error = '';
    if (empty($password) || empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'Please enter all mandatory / compulsory fields...';
    } else {
        $email = (isset($email) && $email != '') ? $email : '';
        $mobile_no = (isset($mobile_no) && $mobile_no != '') ? $mobile_no : '';
        $webshop_obj = new DbCommonFeature();
        $webmail_obj = new DbEmailFeature();


        $IsEmailExists = $webshop_obj->CustomerDetailsByEmailId($email, $mobile_no);
        if ($IsEmailExists !== false) {
            if($lang_code == "fr"){
                $error = 'Un utilisateur est déjà enregistré avec cette adresse e-mail ou ce numéro de téléphone mobile.';
            }else{
                $error = 'User already registered with this email address OR Mobile number';
            }
        } else {

            $time = time();
            $status = 1;
            $HashPassword = md5($password);
            $insert_user = array(
                "first_name" => $first_name,
                "last_name" => $last_name,
                "phone_prefix" => $phone_prefix,
                "mobile_no" => $mobile_no,
                "email_id" => $email,
                "status" => $status,
                "password" => $HashPassword,
                "country_code" => $country_code,
                "created_at" => $time,
                'ip' => $ip
            );


            $insert_customer = $webshop_obj->insert_customer($insert_user);
            if ($insert_customer != false) {
                $webshop_name = 'Yellow Markets';
                $site_logo = '';
                $name = $first_name . ' ' . $last_name;
                $email_code = "customer-register-successful";
                $TempVars = array('##CUSTOMERNAME##', '##WEBSHOPNAME##');
                $DynamicVars = array($name, $webshop_name);
                $CommonVars = array($site_logo, $webshop_name);

                $emailSendStatusFlag = $webmail_obj->get_email_code_status($email_code);
                if ($emailSendStatusFlag == 1) {
                    $send_email = $webmail_obj->sendCommonHTMLEmail($email, $email_code, $TempVars, $DynamicVars, $webshop_name, '', $CommonVars, $lang_code);
                }
            } else {
                if($lang_code == "fr"){
                    $error = "L'inscription a échoué.";
                }else{
                    $error = 'Registration failed.';
                }
            }
        }
    }
    if ($error !== '') {
        $message['statusCode'] = '500';
        $message['is_success'] = 'false';
        $message['message'] = $error;
        exit(json_encode($message));
    } else {
        
        if($lang_code == "fr"){
            $msg = "Le compte a été créé avec succès.";
        }else{
            $msg = 'Account created successfully.';
        }

        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['message'] = $msg;
        exit(json_encode($message));
    }
});

<?php

/**
 * Documentação
 * https://asaasv3.docs.apiary.io/#
 * 
 * Sandbox
 * https://sandbox.asaas.com/
 * https://sandbox.asaas.com/dashboard/index
 * 
 * api key sandbox
 * 
 */


define("ASAAS_API_KEY_PRO", '$aact_YTU5YTE0M2M2N2I4MTliNzk0YTI5N2U5MzdjNWZmNDQ6OjAwMDAwMDAwMDAwMDAyMjYyOTg6OiRhYWNoXzcwNjQxMTBiLWFkYzMtNDUyZS05ZTBmLTNiNDYyMWIzZWRlZA==');
define("ASAAS_API_KEY_SAN", '$aact_YTU5YTE0M2M2N2I4MTliNzk0YTI5N2U5MzdjNWZmNDQ6OjAwMDAwMDAwMDAwMDAwNDM3MTk6OiRhYWNoXzQ5NTM3OWY4LWJiYmItNGYwNy1hMGQ2LTgyODcxNzAwZGFiMA==');

define("ASAAS_URL_PRO", "https://www.asaas.com/api/v3/");
define("ASAAS_URL_SAN", "https://sandbox.asaas.com/api/v3/");

define("ASAAS_API_TRA", ASAAS_API_KEY_SAN);
define("ASAAS_URL_TRA", ASAAS_URL_SAN);

define("ASAAS_LOGS", "logs/");


/**
 * asaasLogs function
 * Função responsável por registrar logs em pasta específica
 *
 * @param [type] $logs
 * @return void
 */
function asaasLogs($logs, $file=NULL) {
    if($file == NULL) {
        $file = date("Y-m-d").".txt";
    }
    $fileOp = fopen(ASAAS_LOGS.$file, "a");
    fwrite($fileOp, "[".date("Y-m-d H:i:s")."] ".$logs."\r\n");
    fclose($fileOp);
}



/**
 * asaasNovoCliente function
 * Função responsável por criar nova conta de usuário no asaas
 * 
 * @param array $dados
 * @return array $status, $costumerID
 */
function asaasNovoCliente(array $dados) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, ASAAS_URL_TRA."customers");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    
    curl_setopt($ch, CURLOPT_POST, TRUE);

    asaasLogs("====== asaasNovoCliente ======");
    asaasLogs(ASAAS_URL_TRA."customers");

    $json = "{
        \"name\": \"".$dados["name"]."\",
        \"email\": \"".$dados["email"]."\",
        \"mobilePhone\": \"".$dados["mobilePhone"]."\",
        \"cpfCnpj\": \"".$dados["cpfCnpj"]."\",
        \"postalCode\": \"".$dados["postalCode"]."\",
        \"addressNumber\": \"".$dados["addressNumber"]."\",
        \"complement\": \"".$dados["complement"]."\",
        \"address\": \"".$dados["address"]."\",
        \"province\": \"".$dados["province"]."\",
        \"externalReference\": \"".$dados["externalReference"]."\",
        \"notificationDisabled\": false,
    }";

    asaasLogs($json);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Content-Type: application/json",
      "access_token: ".ASAAS_API_TRA
    ));
    
    $response = curl_exec($ch);
    curl_close($ch);

    asaasLogs($response);

    $response = json_decode($response);

    if($response->id != "") {
        $out = array(
            "status" => "200",
            "costumerID" => $response->id,
        );
    } else {
        $out = array(
            "status" => "ERRO",
            "costumerID" => "",
        );
    }

    return $out;
}

/**
 * asaasEditaCliente function
 * Função responsável por editar uma conta de usuário no asaas
 * 
 * @param array $dados
 * @return array $status, $costumerID
 */
function asaasEditaCliente(array $dados) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, ASAAS_URL_TRA."customers/".$dados["costumerID"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_POST, TRUE);

    asaasLogs("====== asaasEditaCliente ======");
    asaasLogs(ASAAS_URL_TRA."customers/".$dados["costumerID"]);

    $json = "{
        \"name\": \"".$dados["name"]."\",
        \"email\": \"".$dados["email"]."\",
        \"mobilePhone\": \"".$dados["mobilePhone"]."\",
        \"cpfCnpj\": \"".$dados["cpfCnpj"]."\",
        \"postalCode\": \"".$dados["postalCode"]."\",
        \"addressNumber\": \"".$dados["addressNumber"]."\",
        \"complement\": \"".$dados["complement"]."\",
        \"address\": \"".$dados["address"]."\",
        \"province\": \"".$dados["province"]."\",
    }";

    asaasLogs($json);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "access_token: ".ASAAS_API_TRA
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    asaasLogs($response);

    $response = json_decode($response);

    if($response->id != "") {
        $out = array(
            "status" => "200",
            "costumerID" => $response->id,
        );
    } else {
        $out = array(
            "status" => "ERRO",
            "costumerID" => "",
        );
    }

    return $out;
}


/**
 * asaasEfetuaCompra function
 * Função responsável por efetuar compra no asaas
 *
 * @param [array] $dados
 * @return array $status, $billingId
 */
function asaasEfetuaCompra(array $dados) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, ASAAS_URL_TRA."payments/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_POST, TRUE);

    asaasLogs("====== asaasEfetuaCompra ======");
    asaasLogs(ASAAS_URL_TRA."payments/");

    if(array_key_exists("creditCard", $dados)) {

        $json = "{
            \"customer\": \"".$dados["costumerID"]."\",
            \"billingType\": \"".$dados["billingType"]."\",
            \"dueDate\": \"".$dados["dueDate"]."\",
            \"description\": \"".$dados["description"]."\",
            \"externalReference\": \"".$dados["externalReference"]."\",
            \"installmentCount\": \"".$dados["installmentCount"]."\",
            \"installmentValue\": \"".$dados["installmentValue"]."\",
            \"postalService\": false,
            \"remoteIp\": \"".$dados["remoteIp"]."\",
            \"creditCard\": {
                \"holderName\": \"".$dados["creditCard"]["holderName"]."\",
                \"number\": \"".$dados["creditCard"]["number"]."\",
                \"expiryMonth\": \"".$dados["creditCard"]["expiryMonth"]."\",
                \"expiryYear\": \"".$dados["creditCard"]["expiryYear"]."\",
                \"ccv\": \"".$dados["creditCard"]["ccv"]."\",
            },
            \"creditCardHolderInfo\": {
                \"name\": \"".$dados["creditCardHolderInfo"]["name"]."\",
                \"email\": \"".$dados["creditCardHolderInfo"]["email"]."\",
                \"cpfCnpj\": \"".$dados["creditCardHolderInfo"]["cpfCnpj"]."\",
                \"postalCode\": \"".$dados["creditCardHolderInfo"]["postalCode"]."\",
                \"addressNumber\": \"".$dados["creditCardHolderInfo"]["addressNumber"]."\",
                \"addressComplement\": \"".$dados["creditCardHolderInfo"]["addressComplement"]."\",
                \"mobilePhone\": \"".$dados["creditCardHolderInfo"]["mobilePhone"]."\",
            },
        }";

        /*
            \"split\": [{
                \"walletId\": \"".$dados["split_walletId"]."\",
                \"fixedValue\": \"".$dados["fixedValue"]."\",
            }],
        */

        asaasLogs($json);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    } else {

        $json = "{
            \"customer\": \"".$dados["costumerID"]."\",
            \"billingType\": \"".$dados["billingType"]."\",
            \"dueDate\": \"".$dados["dueDate"]."\",
            \"value\": ".$dados["value"].",
            \"description\": \"".$dados["description"]."\",
            \"externalReference\": \"".$dados["externalReference"]."\",
            \"installmentCount\": \"".$dados["installmentCount"]."\",
            \"totalValue\": \"".$dados["installment_totalValue"]."\",
            \"postalService\": false,
        }";

        /*
            \"split\": [{
                \"walletId\": \"".$dados["split_walletId"]."\",
                \"fixedValue\": \"".$dados["fixedValue"]."\",
            }],
        */

        asaasLogs($json);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "access_token: ".ASAAS_API_TRA
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    asaasLogs($response);

    $response = json_decode($response);
    
    if(!empty($response->errors)) {
        $out = array(
            "status" => "ERRO",
            "message" => $response->errors[0]->description,
        );
    } else {
        $out = array(
            "status"            => "200",
            "id"                => $response->id,
            "dateCreated"       => $response->dateCreated,
            "customer"          => $response->customer,
            "installment"       => $response->installment,
            "value"             => $response->value,
            "netValue"          => $response->netValue,
            "description"       => $response->description,
            "billingType"       => $response->billingType,
            "statusDesc"        => $response->status,
            "dueDate"           => $response->dueDate,
            "originalDueDate"   => $response->originalDueDate,
            "invoiceUrl"        => $response->invoiceUrl,
            "invoiceNumber"     => $response->invoiceNumber,
            "externalReference" => $response->externalReference,
            "nossoNumero"       => $response->nossoNumero,
            "bankSlipUrl"       => $response->bankSlipUrl,
        );
    }

    return $out;

}

<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| This application is installed by the Composer,
| that provides a class loader automatically.
| Use it to seamlessly and feel free to relax.
|
*/

require __DIR__.'/bootstrap.php';

/**
 * Cria o objeto de integração com a Cielo usando o ambiente de desenvolvimento
 * @var Cielo
 * CieloMode::DEPLOYMENT
 * CieloMode::PRODUCTION
 */

use Dso\Cielo\CieloMode;

$mode = Dso\Cielo\CieloMode::DEPLOYMENT;

$cielo = new Dso\Cielo\Cielo($mode);

/**
 * Define o código de afiliação.
 * O código abaixo é usado no ambiente de testes para poder recuperar
 * os dados do cartão do cliente dentro da loja
 * Test BuyCielo = 1001734898
 * Test BuyLoja = 1006993069
 */
$cielo->setAffiliationCode('1006993069');

/**
 * Define a chave de afiliação.
 * A chave abaixo é usada no ambiente de testes para poder recuperar
 * os dados do cartão do cliente dentro da loja
 * Test BuyCielo = e84827130b9837473681c2787007da5914d6359947015a5cdb2b8843db0fa832
 * Test BuyLoja = 25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3
 */
$cielo->setAffiliationKey('25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3');

/**
 * Define a url de retorno
 * O código abaixo é usado para retornar as informações da transação
 */
$cielo->setReturnURL('http://localhost/cielo/public/?mode=retorno');

/**
 * Número do pedido
 * @var string
 */
$orderNumber = substr(str_shuffle('0123456789'), 0, 8);

/**
 * Valor do pedido, esse valor deve ser um inteiro onde os últimos
 * dois dígitos são os centavos, no caso o valor 10000 corresponde
 * ao valor de R$ 100,00
 * ATENÇÃO (Ambiente de teste): O valor do pedido além de seguir o formato sem pontos
 * ou vírgulas decimais, deve terminar em “00”, caso contrário,
 * a autorização será sempre negada.
 * Exemplo: R$ 15,00 deve ser formatado como “1500”.
 * @var integer
 */
$orderValue = 12300;

/**
 * Tipo do pagamento, a vista, débito, parcelado pela loja ou pelo banco,
 * @var integer
 */
//$paymentProduct = Dso\Cielo\PaymentProduct::ONE_TIME_PAYMENT; // Crédito a Vista

//$paymentProduct = Dso\Cielo\PaymentProduct::INSTALLMENTS_BY_AFFILIATED_MERCHANTS; // Parcelado pela Loja

//$paymentProduct = Dso\Cielo\PaymentProduct::INSTALLMENTS_BY_CARD_ISSUERS; // Parcelado pela Administradora

$paymentProduct = Dso\Cielo\PaymentProduct::DEBIT; // Débito

$parcelas = 1;

/**
 * Número de parcelas que a compra será dividida
 * @var integer
 */
if ($paymentProduct == 1 || $paymentProduct == 'A') {
    $parcels = 1;
} else {
    $parcels = (int) $parcelas;
}

/**
 * Buy Page Cielo = BuyCielo
 * Buy Page Loja = BuyLoja
 * Consulta
 */
if (empty($_REQUEST['mode'])) {
    define('BUYMODE', 'BuyLoja');
} elseif ($_REQUEST['mode'] == 'retorno') {
    define('BUYMODE', 'retorno');
} elseif ($_REQUEST['mode'] == 'consulta') {
    define('BUYMODE', 'consulta');
} elseif ($_REQUEST['mode'] == 'cielo') {
    define('BUYMODE', 'BuyCielo');
}

/**
 * Tipo de cartão
 * @var string
 */
$cartao = Dso\Cielo\CreditCard::VISA;

/**
 * Nome do portador do cartão
 * @var string
 */
$holderName = 'cielo';

/**
 * Número do cartão do cliente
 * @var string
 */
$cardNumber = '4551870000000183'; //preg_replace('/\D/', '', $_POST['cardNumber']);

/**
 * Data de expiração do cartão no formato yyyymm
 * @var string
 */
$cardExpiration = '201806'; //preg_replace('/\D/', '', $_POST['cardExpiration']);

/**
 * Três dígitos do código de segurança que ficam no verso do cartão
 * @var integer
 */

$_POST['securityCode'] = 123;

if (isset($_POST['securityCode'])) {
    $securityCode = preg_replace('/\D/', '', $_POST['securityCode']);
} else {
    $securityCode = null;
}

/**
 * Verifica se Código de Segurança foi informado e ajusta o indicador corretamente
 * Indicador do código de segurança
 * @var integer
 */
if ($securityCode == null || $securityCode == "") {
    $indicator = 0;
} elseif ($cartao == Dso\Cielo\CreditCard::MASTER_CARD) {
    $indicator = 1;
} else {
    $indicator = 1;
}

switch (BUYMODE) {
    case 'BuyCielo':
        var_dump('BuyCielo');
        $transaction = $cielo->automaticCapture()
                             ->buildTransactionRequest($cartao, $orderNumber, $orderValue, $paymentProduct, $parcels)
                             ->call();
        /**
         * Dados da autorização
         * @var AuthorizationNode
         */
        $authorization = $transaction->getAuthorization();

        echo "<pre>";

        var_dump($transaction->getPan());
        var_dump($transaction->getStatus());
        var_dump($transaction->getTID());
        var_dump($cielo->__getLastResponse()); //Retorna o XML de resposta

        echo "</pre>";

        break;
    case 'consulta':
        var_dump('consulta');
        $tid = empty($_SESSION['tid']) ? $_REQUEST['tid'] : $_SESSION['tid'];
        $transaction = $cielo->buildQueryTransaction($tid)->call();

        echo "<pre>";
            var_dump($cielo->__getLastRequest()); //Recupera o XML de requisição
            var_dump($cielo->__getLastResponse()); //Recupera o XML de resposta
        echo "</pre>";
        break;
    case 'retorno':
        var_dump('retorno');
        echo "<pre>";
        try {
            $tid = empty($_SESSION['tid']) ? $_REQUEST['tid'] : $_SESSION['tid'];
            $transaction = $cielo->buildQueryTransaction($tid)->call();
            echo '<pre>';var_dump($transaction);echo '</pre>';
            var_dump($transaction->getStatus());
            if ($transaction->getStatus() == 2 || $transaction->getStatus() == 3) {
                $transaction = $cielo->buildAuthorizationTIDRequest($transaction->getTID())->call();

                if ($transaction->getStatus() == 6) {
                    var_dump('Trasação Capturada');
                }
            } else {
                var_dump('Trasação Já Capturada');
            }
            var_dump($cielo->__getLastRequest()); //Recupera o XML de requisição
            var_dump($cielo->__getLastResponse()); //Recupera o XML de resposta
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

        echo "</pre>";
        break;
    case 'BuyLoja':
    default:
        var_dump('BuyLoja');
        echo "<pre>";

        /**
         * O primeiro passo é requerir um TID para a autorização direto na loja
         * Esse passo é necessário para garantir que uma transação não seja autorizada
         * mais de uma vez, caso um timeout de conexão ou algum problema de rede ocorra
         * @var string
         */
        $tid = $cielo->buildTIDRequest($cartao, $paymentProduct)->call()->getTID();

        echo $cielo->__getLastRequest(); //Recupera o XML de requisição
        echo $cielo->__getLastResponse(); //Recupera o XML de resposta

        /**
         * Define se será feita a autorização automática, seu valor pode ser um dos seguinte:
         * 0 – Não autorizar (somente autenticar).
         * 1 – Autorizar somente se autenticada. O cliente é redirecionado para a operadora do cartão
         * 2 – Autorizar autenticada e não autenticada. O cliente é redirecionado para a operadora do cartão
         * 3 – Autorizar sem passar por autenticação (somente para crédito). O cliente não é redirecionado para a operadora do cartão
         */
        $cielo->setAuthorization(2);

        /**
         * Cria a transação com autenticaçao dentro da loja, fazendo captura automática
         * @var Transaction
         */
        $transaction = $cielo
                ->buildAuthenticationRequest(
                    $cartao,
                    $cardNumber,
                    $cardExpiration,
                    $indicator,
                    $securityCode,
                    $holderName,
                    $orderNumber,
                    $orderValue,
                    $paymentProduct,
                    $parcels,
                    null)
                ->call();

        /**
         * Dados da autenticaçao
         * @var AuthenticationNode
         */
        $authentication = $transaction->getAuthentication();
        //echo $transaction->getTID();

        $validateECI = false;
        if ($authentication) {

            /**
             * Verifica se foi retornado o campo ECI
             * @var integer
             */
            $eci = $authentication->getECI();

            $eci_parse = ECI::parse($eci);

            if ($eci_parse == 12) {
                // authorize 2
                $eci_parse = ECI::UNAUTHENTICATED;
                // authorize 3
                //$eci_parse = ECI::AFFILIATED_DID_NOT_SEND_AUTHENTICATION;
            }

            $validateECI = ECI::value($eci_parse, $cartao);

            var_dump($authentication);

            var_dump( $authentication->getECI() );
            var_dump( $authentication->getCode() );
            var_dump( $authentication->getDateTime() );
            var_dump( $authentication->getMessage() );
            var_dump( $authentication->getValue() );
        }

        /**
         * Cria a transação com autorização dentro da loja, fazendo captura automática
         * @var Transaction
         */
        /*
        $transaction = $cielo
                ->automaticCapture()
                ->buildAuthorizationRequest( $tid , $cartao , $cardNumber , $cardExpiration , $indicator , $securityCode , $orderNumber , $orderValue , $paymentProduct , $parcels )
                ->call();
                */

        /**
         * Dados da autorização
         * @var AuthorizationNode
         */
        //$authorization = $transaction->getAuthorization();

        //var_dump($authorization);

        /*
        var_dump( $authorization->getArp() );
        var_dump( $authorization->getCode() );
        var_dump( $authorization->getDateTime() );
        var_dump( $authorization->getLR() );
        var_dump( $authorization->getMessage() );
        var_dump( $authorization->getValue() );
        */

        var_dump($transaction);

        echo $cielo->__getLastRequest(); //Recupera o XML de requisição
        echo $cielo->__getLastResponse(); //Recupera o XML de resposta

        var_dump($tid);

        $tid = $transaction->getTID();

        var_dump($tid);

        $_SESSION['tid'] = $tid;

        echo "</pre>";

        break;
}

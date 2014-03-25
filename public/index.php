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
$mode = Dso\Cielo\CieloMode::DEPLOYMENT;

$cielo = new Dso\Cielo\Cielo( $mode );

/**
 * Define o código de afiliação.
 * O código abaixo é usado no ambiente de testes para poder recuperar
 * os dados do cartão do cliente dentro da loja
 * Test BuyCielo = 1001734898
 * Test BuyLoja = 1006993069
 */
$cielo->setAffiliationCode( '1006993069' );

/**
 * Define a chave de afiliação.
 * A chave abaixo é usada no ambiente de testes para poder recuperar
 * os dados do cartão do cliente dentro da loja
 * Test BuyCielo = e84827130b9837473681c2787007da5914d6359947015a5cdb2b8843db0fa832
 * Test BuyLoja = 25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3
 */
$cielo->setAffiliationKey( '25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3' );

/**
 * Define a url de retorno
 * O código abaixo é usado para retornar as informações da transação
 */
$cielo->setReturnURL( 'http://localhost/cielo/public/retorno.php' );

/**
 * Número do pedido
 * @var string
 */
$orderNumber = '1234';

/**
 * Valor do pedido, esse valor deve ser um inteiro onde os últimos
 * dois dígitos são os centavos, no caso o valor 10000 corresponde
 * ao valor de R$ 100,00
 * @var integer
 */
$orderValue = 10000;

/**
 * Tipo de cartão
 * @var string
 */
$cartao = Dso\Cielo\CreditCard::MASTER_CARD;

/**
 * Tipo do pagamento, a vista, débito, parcelado pela loja ou pelo banco,
 * @var integer
 */
$paymentProduct = Dso\Cielo\PaymentProduct::ONE_TIME_PAYMENT; // Crédito a Vista

//$paymentProduct = PaymentProduct::INSTALLMENTS_BY_AFFILIATED_MERCHANTS; // Parcelado pela Loja

//$paymentProduct = PaymentProduct::INSTALLMENTS_BY_CARD_ISSUERS; // Parcelado pela Administradora

//$paymentProduct = PaymentProduct::DEBIT; // Débito

/**
 * Número de parcelas que a compra será dividida
 * @var integer
 */
if($paymentProduct==1 or $paymentProduct=='A'){
    $parcels = 1;
}else{
    $parcels = (int)$parcelas;
}

/**
 * O primeiro passo é requerir um TID para a autorização direto na loja
 * Esse passo é necessário para garantir que uma transação não seja autorizada
 * mais de uma vez, caso um timeout de conexão ou algum problema de rede ocorra
 * @var string
 */
$tid = $cielo->buildTIDRequest( $cartao , $paymentProduct )->call()->getTID();

/**
 * Buy Page Cielo = BuyCielo
 * Buy Page Loja = BuyLoja
 */
define('BuyMode', 'BuyLoja');

switch (BuyMode) {
    case 'BuyCielo':
        $transaction = $cielo->automaticCapture()
                         ->buildTransactionRequest( $cartao , $orderNumber , $orderValue , $paymentProduct , $parcels )
                         ->call();
        /**
         * Dados da autorização
         * @var AuthorizationNode
         */
        $authorization = $transaction->getAuthorization();

        echo "<pre>";

        var_dump( $transaction->getPan() );
        var_dump( $transaction->getStatus() );
        var_dump( $transaction->getTID() );
        var_dump( $cielo->__getLastResponse() ); //Retorna o XML de resposta

        echo "</pre>";

        break;
    case 'BuyCielo':
    default:

        /**
         * Número do cartão do cliente
         * @var string
         */
        $cardNumber = '6011020000245045'; //preg_replace('/\D/', '', $_POST['cardNumber']);

        /**
         * Data de expiração do cartão no formato yyyymm
         * @var string
         */
        $cardExpiration = '201805'; //preg_replace('/\D/', '', $_POST['cardExpiration']);

        /**
         * Três dígitos do código de segurança que ficam no verso do cartão
         * @var integer
         */

        $_POST['securityCode'] = 123;

        if(isset($_POST['securityCode'])){
            $securityCode = preg_replace('/\D/', '', $_POST['securityCode']);
        }else{
            $securityCode = null;
        }
        
        /**
         * Verifica se Código de Segurança foi informado e ajusta o indicador corretamente
         * Indicador do código de segurança
         * @var integer
         */
        if ($securityCode == null || $securityCode == "")
        {
            $indicator = 0;
        }
        else if ($cartao == Dso\Cielo\CreditCard::MASTER_CARD)
        {
            $indicator = 1;
        }
        else
        {
            $indicator = 1;
        }

        /**
         * Cria a transação com autenticaçao dentro da loja, fazendo captura automática
         * @var Transaction
         */
        $transaction = $cielo
                ->automaticCapture()
                ->buildAuthenticationRequest( $cartao , $cardNumber , $cardExpiration , $indicator , $securityCode , null, $orderNumber , $orderValue , $paymentProduct , $parcels , null )
                ->call();

        /**
         * Dados da autenticaçao
         * @var AuthenticationNode
         */
        $authentication = $transaction->getAuthentication();
        echo "<pre>";
            
            var_dump( $transaction->getPan() );
            var_dump( $transaction->getStatus() );
            var_dump( $transaction->getTID() );

            var_dump($authentication);
            /*
            var_dump( $authentication->getECI() );
            var_dump( $authentication->getCode() );
            var_dump( $authentication->getDateTime() );
            var_dump( $authentication->getMessage() );
            var_dump( $authentication->getValue() );
            */

            echo $cielo->__getLastRequest(); //Recupera o XML de requisição
            echo $cielo->__getLastResponse(); //Recupera o XML de resposta

        echo "</pre>";

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
        /*
        $authorization = $transaction->getAuthorization();

        echo "<pre>";
            
            var_dump( $transaction->getPan() );
            var_dump( $transaction->getStatus() );
            var_dump( $transaction->getTID() );

            var_dump( $authorization->getArp() );
            var_dump( $authorization->getCode() );
            var_dump( $authorization->getDateTime() );
            var_dump( $authorization->getLR() );
            var_dump( $authorization->getMessage() );
            var_dump( $authorization->getValue() );

            echo $cielo->__getLastRequest(); //Recupera o XML de requisição
            echo $cielo->__getLastResponse(); //Recupera o XML de resposta

        echo "</pre>";
        */
        break;
}
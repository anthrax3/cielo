<?php

namespace Dso\Cielo\Request;

use \DOMDocument;
use \DOMElement;
use Dso\Cielo\Nodes\AbstractCieloNode;
use Dso\Cielo\Transaction;

/**
 * @author      João Batista Neto
 * @brief       Classes relacionadas ao webservice da Cielo
 * @package     dso.cielo.request
 */

/**
 * @brief               Requisição de Autorizacao Posterior.
 * @details
 * <p>
 * Com base na resposta de autenticação, autenticada ou não-autenticada, e nas escolhas efetuadas na
 * criação da transação, a autorização é a próxima etapa. Nesse cenário ela é cunhada de autorização
 * automática. Embora essa escolha caiba a loja virtual, em conjunto são consideradas outras regras:
 * @li Se o portador não se autenticou com sucesso, ela não é executada.
 * @li Se o portador autenticou-se com sucesso, ela pode ser executada.
 * @li Se o emissor não forneceu mecanismos de autenticação, ela pode ser executada.
 * @li Se a resposta do emissor não pôde ser validada, ela não é executada.
 * </p>
 *
 * @attention É nessa etapa que o saldo disponível do cartão do comprador é sensibilizado caso a transação
 * tenha sido autorizada.
 *
 * @ingroup             Cielo
 * @class               AuthorizationRequest
 */
class AuthorizationTIDRequest extends AbstractCieloNode {
    /**
     * ID da transação
     * @var         string
     */
    private $tid;

    /**
     * Cria o nó XML que representa o objeto ou conjunto de objetos na composição
     * @return      string
     * @see         Cielo::createXMLNode()
     * @throws      BadMethodCallException Se a URL de retorno não tiver sido especificada
     * @throws      BadMethodCallException Se os dados do pedido não tiver sido especificado
     */
    public function createXMLNode() {
            if (  !empty( $this->tid ) ) {
                    $dom = new DOMDocument( '1.0' , 'UTF-8' );
                    $dom->loadXML( parent::createXMLNode() );
                    $dom->encoding = 'UTF-8';

                    $namespace = $this->getNamespace();
                    $query = $dom->getElementsByTagNameNS( $namespace , $this->getRootNode() )->item( 0 );
                    $EcData = $dom->getElementsByTagNameNS( $namespace , 'dados-ec' )->item( 0 );

                    if ( $EcData instanceof DOMElement ) {
                            $tid = $dom->createElement( 'tid' , $this->tid );
                            $query->insertBefore( $tid , $EcData );

                    } else {
                            throw new BadMethodCallException( 'O nó dados-ec precisa ser informado.' );
                    }

                    return $dom->saveXML();
            } else {
                    throw new BadMethodCallException( 'O ID da transação deve ser informado.' );
            }
    }

    /**
     * Faz a chamada da requisição de autenticação no webservice da Cielo
     * @return      Transaction
     * @see         Cielo::call()
     */
    public function call() {
            return new Transaction( parent::call() );
    }

    /**
     * Define o identificador da transação
     * @param       string $tid
     */
    public function setTID( $tid ) {
            $this->tid = $tid;
    }

    /**
     * Recupera o ID do nó raiz
     * @return      string
     */
    protected function getId() {
            return 6;
    }

    /**
     * Recupera o nome do nó raiz do XML que será enviado à Cielo
     * @return      string
     */
    protected function getRootNode() {
            return 'requisicao-autorizacao-tid';
    }
}
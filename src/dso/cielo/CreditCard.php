<?php

namespace Dso\Cielo;

/**
 * @author      João Batista Neto
 * @brief       Classes relacionadas ao webservice da Cielo
 * @package     dso.cielo
 */

/**
 * Bandeira do cartão
 * @ingroup     Cielo
 * @interface   CreditCard
 */
interface CreditCard {
    /**
     * Cartão Visa
     * CV, PL, PA e D
     */
    const VISA = 'visa';

    /**
     * Cartão MarterCard
     * CV, PL, PA e D
     */
    const MASTER_CARD = 'mastercard';

    /**
     * Cartão Elo
     * CV, PL e D
     */
    const ELO = 'elo';

    /**
     * Cartão American Express
     * CV, PL, e PA
     */
    const AMERICAN_EXPRESS = 'amex';

    /**
     * Cartão Diners Club Internacional
     * CV, PL, e PA
     */
    const DINERS_CLUB = 'diners';

    /**
     * Cartão Discover
     * CV
     */
    const DISCOVER = 'discover';

    /**
     * Cartão JCB
     * CV, PL e PA
     */
    const JCB = 'jcb';

    /**
     * Cartão AURA
     * CV, PL e PA
     */
    const AURA = 'aura';
}
<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum PaymentMethodType: string
{
    case AcssDebit = "acss_debit";
    case Affirm = "affirm";
    case AfterpayClearpay = "afterpay_clearpay";
    case Alipay = "alipay";
    case AmazonPay = "amazon_pay";
    case AuBecsDebit = "au_becs_debit";
    case BacsDebit = "bacs_debit";
    case Bancontact = "bancontact";
    case Blik = "blik";
    case Boleto = "boleto";
    case Card = "card";
    case CardPresent = "card_present";
    case Cashapp = "cashapp";
    case CustomerBalance = "customer_balance";
    case Eps = "eps";
    case Fpx = "fpx";
    case Giropay = "giropay";
    case Grabpay = "grabpay";
    case Ideal = "ideal";
    case InteracPresent = "interac_present";
    case Klarna = "klarna";
    case Konbini = "konbini";
    case Link = "link";
    case Mobilepay = "mobilepay";
    case Multibanco = "multibanco";
    case Oxxo = "oxxo";
    case P24 = "p24";
    case Paynow = "paynow";
    case Paypal = "paypal";
    case Pix = "pix";
    case Promptpay = "promptpay";
    case RevolutPay = "revolut_pay";
    case SepaDebit = "sepa_debit";
    case Sofort = "sofort";
    case Swish = "swish";
    case Twint = "twint";
    case UsBankAccount = "us_bank_account";
    case WechatPay = "wechat_pay";
    case Zip = "zip";
}

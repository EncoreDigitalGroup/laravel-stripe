import {loadStripe} from "@stripe/stripe-js";
import axios from "axios";

export class FinancialConnection {
    private readonly stripePublicKey: string;
    private readonly stripeSessionSecret: string;
    private readonly stripeCustomerId: string;
    private readonly redirectSuccessUrl: string;
    private readonly redirectErrorUrl: string;
    private readonly postSuccessUrl: string;
    private readonly publicSecurityKey: string;
    private readonly privateSecurityKey: string;

    constructor(
        stripePublicKey: string,
        stripeSessionSecret: string,
        stripeCustomerId: string,
        redirectSuccessUrl: string,
        redirectErrorUrl: string,
        postSuccessUrl: string,
        publicSecurityKey: string,
        privateSecurityKey: string,
    ) {
        this.stripePublicKey = stripePublicKey;
        this.stripeSessionSecret = stripeSessionSecret;
        this.stripeCustomerId = stripeCustomerId;
        this.redirectSuccessUrl = redirectSuccessUrl;
        this.redirectErrorUrl = redirectErrorUrl;
        this.postSuccessUrl = postSuccessUrl;
        this.publicSecurityKey = publicSecurityKey;
        this.privateSecurityKey = privateSecurityKey;
    }

    async initialize(): Promise<void> {
        try {
            const stripe = await loadStripe(this.stripePublicKey);

            if (stripe === null) {
                this.fail();
                return;
            }

            const financialConnectionResult = await stripe.collectFinancialConnectionsAccounts({
                clientSecret: this.stripeSessionSecret,
            });

            console.info(this.postSuccessUrl);

            if (financialConnectionResult.financialConnectionsSession === undefined) {
                this.fail();
                return;
            }

            const financialConnection = financialConnectionResult.financialConnectionsSession;

            if (financialConnection.accounts.length === 0) {
                this.fail();
                return;
            }

            const connectedAccountsPayload = {
                securityKeys: {
                    publicKey: this.publicSecurityKey.toString(),
                    privateKey: this.privateSecurityKey.toString(),
                },
                stripeCustomerId: this.stripeCustomerId,
                accounts: financialConnection.accounts,
            };

            try {
                await axios.post(this.postSuccessUrl, JSON.stringify(connectedAccountsPayload));
                this.success();
            } catch (error) {
                this.fail();
            }
        } catch (error) {
            this.fail();
        }
    }

    redirect(success = true): void {
        if (success) {
            window.location.href = this.redirectSuccessUrl;
            return;
        }

        window.location.href = this.redirectErrorUrl;
    }

    fail(): void {
        this.redirect(false);
        return;
    }

    success(): void {
        this.redirect();
        return;
    }
}

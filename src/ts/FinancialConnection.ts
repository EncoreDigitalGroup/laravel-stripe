/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */
import {loadStripe} from "@stripe/stripe-js";
import axios from "axios";

export class FinancialConnection {
    private readonly stripePublicKey: string;
    private readonly stripeSessionSecret: string;
    private readonly redirectSuccessUrl: string;
    private readonly redirectErrorUrl: string;

    constructor(
        stripePublicKey: string,
        stripeSessionSecret: string,
        redirectSuccessUrl: string,
        redirectErrorUrl: string,
    ) {
        this.stripePublicKey = stripePublicKey;
        this.stripeSessionSecret = stripeSessionSecret;
        this.redirectSuccessUrl = redirectSuccessUrl;
        this.redirectErrorUrl = redirectErrorUrl;
    }

    async initialize(): Promise<void> {
        const stripe = await loadStripe(this.stripePublicKey);

        if (!stripe) {
            throw new Error("Failed to initialize Stripe");
        }

        try {
            const publicSecurityKey = document.getElementById("spPublicSecurityKey") as HTMLInputElement;
            const privateSecurityKey = document.getElementById("spPrivateSecurityKey") as HTMLInputElement;

            if (publicSecurityKey === undefined || publicSecurityKey === null) {
                this.fail();
                return;
            }

            if (privateSecurityKey === undefined || privateSecurityKey === null) {
                this.fail();
                return;
            }

            console.info(publicSecurityKey.value);
            console.info(privateSecurityKey.value);

            const financialConnectionResult = await stripe.collectFinancialConnectionsAccounts({
                clientSecret: this.stripeSessionSecret,
            });

            if (financialConnectionResult.financialConnectionsSession === undefined) {
                this.fail();
                return;
            }

            const financialConnection = financialConnectionResult.financialConnectionsSession;

            if (financialConnection.accounts.length === 0) {
                this.fail();
                return;
            }

            console.info(financialConnection);
            return;

            try {
                await axios.post(
                    "/_private/api/financials/bankAccounts/create",
                    JSON.stringify({
                        securityKeys: {
                            publicKey: publicSecurityKey.value,
                            privateKey: privateSecurityKey.value,
                        },
                        accountData: financialConnection,
                    }),
                );
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
    }

    success(): void {
        this.redirect();
    }
}

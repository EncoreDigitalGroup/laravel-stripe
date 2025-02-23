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
    private readonly postSuccessUrl: string;
    private readonly publicSecurityKey: string;
    private readonly privateSecurityKey: string;

    constructor(
        stripePublicKey: string,
        stripeSessionSecret: string,
        redirectSuccessUrl: string,
        redirectErrorUrl: string,
        postSuccessUrl: string,
        publicSecurityKey: string,
        privateSecurityKey: string,
    ) {
        this.stripePublicKey = stripePublicKey;
        this.stripeSessionSecret = stripeSessionSecret;
        this.redirectSuccessUrl = redirectSuccessUrl;
        this.redirectErrorUrl = redirectErrorUrl;
        this.postSuccessUrl = postSuccessUrl;
        this.publicSecurityKey = publicSecurityKey;
        this.privateSecurityKey = privateSecurityKey;
    }

    async initialize(): Promise<void> {
        const stripe = await loadStripe(this.stripePublicKey);

        if (!stripe) {
            throw new Error("Failed to initialize Stripe");
        }

        try {
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

            try {
                await axios.post(
                    this.postSuccessUrl,
                    JSON.stringify({
                        publicSecurityKey: this.publicSecurityKey,
                        privateSecurityKey: this.privateSecurityKey,
                        accounts: financialConnection.accounts,
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

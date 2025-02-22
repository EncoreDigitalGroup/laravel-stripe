/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

import {loadStripe} from "@stripe/stripe-js";

declare global {
    interface Window {
        FinancialConnection: typeof FinancialConnection;
    }
}

export class FinancialConnection {
    private readonly stripePublicKey: string;
    private readonly stripeSessionSecret: string;
    private readonly redirectSuccessUrl: string;
    private readonly redirectErrorUrl: string;

    constructor(
        stripePublicKey: string,
        stripeSessionSecret: string,
        redirectSuccessUrl: string,
        redirectErrorUrl: string
    ) {
        this.stripePublicKey = stripePublicKey;
        this.stripeSessionSecret = stripeSessionSecret;
        this.redirectSuccessUrl = redirectSuccessUrl;
        this.redirectErrorUrl = redirectErrorUrl;
    }

    async initialize(): Promise<void> {
        const stripe = await loadStripe(this.stripePublicKey);
        if (!stripe) {
            throw new Error('Failed to initialize Stripe');
        }

        try {
            await stripe.collectFinancialConnectionsAccounts({
                clientSecret: this.stripeSessionSecret,
            });
            window.location.href = this.redirectSuccessUrl;
        } catch (error) {
            window.location.href = this.redirectErrorUrl;
        }
    }
}

window.FinancialConnection = FinancialConnection;
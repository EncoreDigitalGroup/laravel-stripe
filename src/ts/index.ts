/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

import {FinancialConnection} from "./FinancialConnection";

declare global {
    interface Window {
        FinancialConnection: typeof FinancialConnection;
    }
}

window.FinancialConnection = FinancialConnection;

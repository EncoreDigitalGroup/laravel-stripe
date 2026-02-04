
import {FinancialConnection} from "./FinancialConnection";

declare global {
    interface Window {
        FinancialConnection: typeof FinancialConnection;
    }
}

window.FinancialConnection = FinancialConnection;

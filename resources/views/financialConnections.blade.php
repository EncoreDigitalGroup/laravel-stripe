<script>
    const stripe = new Stripe({{ $stripePublicKey }})
    const financialConnectionsSessionResult = await stripe.collectFinancialConnectionsAccounts({
        clientSecret: {{ $stripeSessionSecret }},
    });
</script>
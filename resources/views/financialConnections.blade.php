<script>
    const stripe = new Stripe("{{ $stripePublicKey }}")
    stripe.collectFinancialConnectionsAccounts({
        clientSecret: "{{ $stripeSessionSecret }}",
    }).then(function (result) {
        console.info(result);
    });
</script>
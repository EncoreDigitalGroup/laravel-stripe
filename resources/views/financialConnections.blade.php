<body></body>
<script>
    const stripe = new Stripe("{{ $stripePublicKey }}");
    stripe.collectFinancialConnectionsAccounts({
        clientSecret: "{{ $stripeSessionSecret }}",
    }).then(function (event) {
        window.location.href = "{{ $redirectSuccessUrl }}"
    }).catch(function (event) {
        window.location.href = "{{ $redirectErrorUrl }}"
    })
</script>
<script src="/js/encoredigitalgroup/common-stripe/financialConnections.js"></script>
<script>
    const stripeConnection = new FinancialConnection(
        "{{ $stripePublicKey }}",
        "{{ $stripeSessionSecret }}",
        "{{ $stripeCustomerId }}",
        "{{ $redirectSuccessUrl }}",
        "{{ $redirectErrorUrl }}",
        "{{ $postSuccessUrl }}",
        "{{ $publicSecurityKey }}",
        "{{ $privateSecurityKey }}"
    );

    stripeConnection.initialize();
</script>
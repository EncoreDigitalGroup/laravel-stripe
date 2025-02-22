<script src="/js/encoredigitalgroup/common-stripe/financialConnections.js"></script>
<script>
    const stripeConnection = new FinancialConnection(
        "{{ $stripePublicKey }}",
        "{{ $stripeSessionSecret }}",
        "{{ $redirectSuccessUrl }}",
        "{{ $redirectErrorUrl }}"
    );
</script>
<script src="/js/encoredigitalgroup/common-stripe/financialConnections.js"></script>
<input type="hidden" id="spPublicSecurityKey" value="{{ $publicSecurityKey }}">
<input type="hidden" id="spPrivateSecurityKey" value="{{ $privateSecurityKey }}">
<script>
    const stripeConnection = new FinancialConnection(
        "{{ $stripePublicKey }}",
        "{{ $stripeSessionSecret }}",
        "{{ $redirectSuccessUrl }}",
        "{{ $redirectErrorUrl }}"
    );

    stripeConnection.initialize();
</script>
(() => {
    window.printDiv = function (divId) {
        const source = document.getElementById(divId);
        if (!source) return;

        const printContents = source.innerHTML;
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;
        doc.open();

        let content = '<html><head><title>Print Preview</title>';
        content += '<style>';
        content += '@media print { body * { visibility: hidden; } #print-area, #print-area * { visibility: visible; } #print-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; } @page { margin: 1cm; size: auto; } }';
        content += 'body { font-family: Arial, sans-serif; }';
        content += '.print-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }';
        content += '.print-table th, .print-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        content += '.print-table th { background-color: #f2f2f2; font-weight: bold; }';
        content += '.print-header { text-align: center; margin-bottom: 2rem; }';
        content += '.print-header h1 { font-size: 18pt; margin: 0; text-transform: uppercase; }';
        content += '.print-header h2 { font-size: 14pt; margin: 5px 0 0; font-weight: normal; }';
        content += '</style>';
        content += '</head><body>' + printContents + '</body></html>';

        doc.write(content);
        doc.close();

        iframe.contentWindow.focus();
        setTimeout(() => {
            iframe.contentWindow.print();
        }, 200);
    };
})();

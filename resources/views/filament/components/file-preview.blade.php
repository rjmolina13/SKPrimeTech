<div class="w-full flex justify-center items-center p-4">
    @if(str_contains($mime, 'image/'))
        <img src="{{ $url }}" alt="Preview" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-md">
    @elseif(str_contains($mime, 'pdf'))
        <iframe src="{{ $url }}" class="w-full h-full rounded-lg shadow-md" style="height: 85vh; width: 100%;" frameborder="0"></iframe>
    @elseif(in_array($mime, [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword'
    ]))
        <div 
            x-data="{
                init() {
                    this.renderDocx();
                },
                renderDocx() {
                    const container = this.$refs.container;
                    fetch('{{ $url }}')
                        .then(res => res.blob())
                        .then(blob => {
                            if (window.docx) {
                                window.docx.renderAsync(blob, container, container)
                                    .then(() => console.log('docx: finished'))
                                    .catch(err => console.error('docx: error', err));
                            } else {
                                // Retry with interval up to 5 seconds
                                let attempts = 0;
                                const maxAttempts = 50; // 50 * 100ms = 5000ms
                                const interval = setInterval(() => {
                                    attempts++;
                                    if (window.docx) {
                                        clearInterval(interval);
                                        window.docx.renderAsync(blob, container, container)
                                            .then(() => console.log('docx: finished'))
                                            .catch(err => console.error('docx: error', err));
                                    } else if (attempts >= maxAttempts) {
                                        clearInterval(interval);
                                        console.error('docx-preview library not loaded');
                                        container.innerHTML = '<p class=\'text-red-500\'>Preview library not loaded. Please refresh the page.</p>';
                                    }
                                }, 100);
                            }
                        });
                }
            }"
            class="w-full h-full bg-white p-4 rounded-lg shadow-md overflow-auto"
            style="height: 85vh;"
        >
            <div x-ref="container" class="w-full h-full"></div>
        </div>
    @elseif(in_array($mime, [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv'
    ]))
        <div 
            x-data="{
                init() {
                    this.renderExcel();
                },
                renderExcel() {
                    const container = this.$refs.container;
                    fetch('{{ $url }}')
                        .then(res => res.arrayBuffer())
                        .then(ab => {
                            if (window.XLSX) {
                                const wb = window.XLSX.read(ab, {type: 'array'});
                                const ws = wb.Sheets[wb.SheetNames[0]];
                                const html = window.XLSX.utils.sheet_to_html(ws, {
                                    id: 'excel-preview-table',
                                    editable: false
                                });
                                container.innerHTML = html;
                                // Basic styling for the table
                                const table = container.querySelector('table');
                                if (table) {
                                    table.classList.add('w-full', 'border-collapse', 'border', 'border-gray-200');
                                    table.querySelectorAll('td, th').forEach(cell => {
                                        cell.classList.add('border', 'border-gray-200', 'p-2', 'text-sm');
                                    });
                                }
                            } else {
                                // Retry with interval up to 5 seconds
                                let attempts = 0;
                                const maxAttempts = 50; // 50 * 100ms = 5000ms
                                const interval = setInterval(() => {
                                    attempts++;
                                    if (window.XLSX) {
                                        clearInterval(interval);
                                        const wb = window.XLSX.read(ab, {type: 'array'});
                                        const ws = wb.Sheets[wb.SheetNames[0]];
                                        const html = window.XLSX.utils.sheet_to_html(ws, {
                                            id: 'excel-preview-table',
                                            editable: false
                                        });
                                        container.innerHTML = html;
                                        // Basic styling for the table
                                        const table = container.querySelector('table');
                                        if (table) {
                                            table.classList.add('w-full', 'border-collapse', 'border', 'border-gray-200');
                                            table.querySelectorAll('td, th').forEach(cell => {
                                                cell.classList.add('border', 'border-gray-200', 'p-2', 'text-sm');
                                            });
                                        }
                                    } else if (attempts >= maxAttempts) {
                                        clearInterval(interval);
                                        console.error('xlsx library not loaded');
                                        container.innerHTML = '<p class=\'text-red-500\'>Preview library not loaded. Please refresh the page.</p>';
                                    }
                                }, 100);
                            }
                        });
                }
            }"
            class="w-full h-full bg-white p-4 rounded-lg shadow-md overflow-auto"
            style="height: 85vh;"
        >
            <div x-ref="container" class="w-full h-full"></div>
        </div>
    @elseif(str_contains($mime, 'text/') && $mime !== 'text/csv')
        <iframe src="{{ $url }}" class="w-full h-full rounded-lg shadow-md bg-white p-4" style="height: 85vh; width: 100%;" frameborder="0"></iframe>
    @else
        <div class="text-center p-8 bg-gray-50 rounded-lg">
            <p class="text-gray-500 mb-4">Preview not available for this file type.</p>
            <p class="text-xs text-gray-400 mb-4">{{ $mime }}</p>
            <a href="{{ $url }}" target="_blank" class="text-primary-600 hover:text-primary-500 font-medium">Download File</a>
        </div>
    @endif
</div>
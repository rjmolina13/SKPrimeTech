<x-filament::page>
    {{ $this->table }}
    
    <x-filament::modal id="print-modal" width="5xl">
        <x-slot name="heading">
            Print Preview
        </x-slot>
        
        <div class="p-4 bg-white rounded-lg shadow-inner overflow-auto max-h-[70vh]">
             <div id="print-area" class="print-content p-8 bg-white text-black">
                <style>
                    @media print {
                        body * {
                            visibility: hidden;
                        }
                        #print-area, #print-area * {
                            visibility: visible;
                        }
                        #print-area {
                            position: absolute;
                            left: 0;
                            top: 0;
                            width: 100%;
                            margin: 0;
                            padding: 0;
                        }
                        @page {
                            margin: 1cm;
                            size: auto;
                        }
                    }
                    .print-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 1rem;
                        font-family: Arial, sans-serif;
                    }
                    .print-table th, .print-table td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    .print-table th {
                        background-color: #f2f2f2;
                        font-weight: bold;
                    }
                    .print-header {
                        text-align: center;
                        margin-bottom: 2rem;
                    }
                    .print-header h1 {
                        font-size: 18pt;
                        margin: 0;
                        text-transform: uppercase;
                    }
                    .print-header h2 {
                        font-size: 14pt;
                        margin: 5px 0 0;
                        font-weight: normal;
                    }
                </style>
                
                <div class="print-header">
                    <h1>List of Municipalities</h1>
                    <h2>Province of Catanduanes</h2>
                </div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th>Municipality Name</th>
                            <th>Zip Code</th>
                            <th>Total Barangays</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->getPrintData() as $record)
                            <tr>
                                <td>{{ $record->name }}</td>
                                <td>{{ $record->code }}</td>
                                <td>{{ $record->barangays_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button type="button" onclick="printDiv('print-area')">
                Print / Save as PDF
            </x-filament::button>
            
            <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'print-modal' })">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

</x-filament::page>

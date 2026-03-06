<x-filament-widgets::widget>
    <x-filament::section>
        <div 
            x-data="{
                chart: null,
                init() {
                    console.log('Chart Widget Init');
                    let checkInterval = setInterval(() => {
                        if (window.ApexCharts) {
                            console.log('ApexCharts found');
                            clearInterval(checkInterval);
                            this.renderChart();
                        } else {
                            console.log('Waiting for ApexCharts...');
                        }
                    }, 500);
                },
                renderChart() {
                    if (this.chart) {
                        this.chart.destroy();
                    }

                    // Clear loading state
                    this.$refs.chart.innerHTML = '';

                    const options = {
                        series: [{
                            name: 'Total Actividades',
                            data: {{ Js::from($chartData['values']) }}
                        }],
                        chart: {
                            type: 'line',
                            height: 350,
                            toolbar: { show: false },
                            fontFamily: 'inherit'
                        },
                        stroke: {
                            curve: 'straight',
                            width: 3
                        },
                        markers: {
                            size: 5,
                            hover: {
                                size: 7
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.45,
                                opacityTo: 0.05,
                                stops: [20, 100, 100, 100]
                            }
                        },
                        xaxis: {
                            categories: {{ Js::from($chartData['labels']) }},
                            labels: {
                                style: {
                                    colors: '#6b7280',
                                    fontSize: '12px'
                                }
                            }
                        },
                        yaxis: {
                            min: 0,
                            labels: {
                                formatter: function (val) {
                                    return val.toFixed(0);
                                },
                                style: {
                                    colors: '#6b7280',
                                    fontSize: '12px'
                                }
                            }
                        },
                        colors: ['#3b82f6'],
                        dataLabels: { enabled: false },
                        grid: {
                            borderColor: '#f3f4f6',
                            strokeDashArray: 4,
                        },
                        title: {
                            text: 'Ejecuciones Totales por Tipo',
                            align: 'left',
                            style: {
                                fontSize: '16px',
                                fontWeight: 600,
                                color: '#374151'
                            }
                        }
                    };

                    this.chart = new ApexCharts(this.$refs.chart, options);
                    this.chart.render();
                }
            }"
            class="w-full"
        >
            <div x-ref="chart" class="w-full h-[350px] flex items-center justify-center text-gray-500">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Cargando gráfico...</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

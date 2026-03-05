import Chart from 'chart.js/auto';
import ApexCharts from 'apexcharts';
import * as d3 from 'd3';

document.addEventListener('DOMContentLoaded', function() {
    // Datos Mock (Replicando imagen)
    const labels = ['OCT 2023', 'NOV 2023', 'DEC 2023', 'JAN 2024', 'FEB 2024', 'MAR 2024'];
    const dataExecutions = [35, 48, 72, 64, 84, 92];
    const dataGoal = [85, 85, 85, 85, 85, 85];

    // --- 1. Chart.js Implementation ---
    const chartJsCanvas = document.getElementById('chartJsCanvas');
    if (chartJsCanvas) {
        new Chart(chartJsCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Executions',
                        data: dataExecutions,
                        borderColor: '#2563eb', // Blue-600
                        backgroundColor: '#2563eb',
                        borderWidth: 2,
                        tension: 0,
                        pointRadius: 4,
                        pointBackgroundColor: '#2563eb'
                    },
                    {
                        label: 'Goal',
                        data: dataGoal,
                        borderColor: '#9ca3af', // Gray-400
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    title: { display: false }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: '#f3f4f6' },
                        ticks: { stepSize: 25 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // --- 2. ApexCharts Implementation ---
    const apexChartEl = document.querySelector("#apexChart");
    if (apexChartEl) {
        const apexOptions = {
            series: [
                { name: 'Total Executions', data: dataExecutions },
                { name: 'Goal', data: dataGoal }
            ],
            chart: {
                type: 'line',
                height: '100%',
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            colors: ['#2563eb', '#9ca3af'],
            stroke: {
                width: [3, 2],
                dashArray: [0, 5],
                curve: 'straight'
            },
            markers: { size: [5, 0] },
            xaxis: {
                categories: labels,
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickAmount: 4
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 0,
            },
            legend: { position: 'top', horizontalAlign: 'right' }
        };
        new ApexCharts(apexChartEl, apexOptions).render();
    }

    // --- 3. D3.js Implementation ---
    const d3Container = d3.select("#d3Chart");
    if (!d3Container.empty()) {
        const margin = {top: 40, right: 20, bottom: 30, left: 40};
        
        // Ensure container has dimensions before rendering
        const containerNode = d3Container.node();
        const containerRect = containerNode.getBoundingClientRect();
        const width = (containerRect.width || 400) - margin.left - margin.right; // Fallback width
        const height = (containerRect.height || 250) - margin.top - margin.bottom; // Fallback height

        const svg = d3Container.append("svg")
            .attr("width", "100%")
            .attr("height", "100%")
            .attr("viewBox", `0 0 ${width + margin.left + margin.right} ${height + margin.top + margin.bottom}`)
            .append("g")
            .attr("transform", `translate(${margin.left},${margin.top})`);

        // X Axis
        const x = d3.scalePoint()
            .domain(labels)
            .range([0, width])
            .padding(0.5);
        
        svg.append("g")
            .attr("transform", `translate(0,${height})`)
            .call(d3.axisBottom(x).tickSize(0))
            .select(".domain").remove();

        // Y Axis
        const y = d3.scaleLinear()
            .domain([0, 100])
            .range([height, 0]);
        
        svg.append("g")
            .call(d3.axisLeft(y).ticks(5).tickSize(-width))
            .call(g => g.select(".domain").remove())
            .call(g => g.selectAll(".tick line").attr("stroke-opacity", 0.1));

        // Line Generator (Executions)
        const lineExec = d3.line()
            .x((d, i) => x(labels[i]))
            .y(d => y(d));

        // Line Generator (Goal)
        const lineGoal = d3.line()
            .x((d, i) => x(labels[i]))
            .y(d => y(d));

        // Draw Goal Line (Dotted)
        svg.append("path")
            .datum(dataGoal)
            .attr("fill", "none")
            .attr("stroke", "#9ca3af")
            .attr("stroke-width", 2)
            .attr("stroke-dasharray", "5,5")
            .attr("d", lineGoal);

        // Draw Execution Line (Solid Blue)
        svg.append("path")
            .datum(dataExecutions)
            .attr("fill", "none")
            .attr("stroke", "#2563eb")
            .attr("stroke-width", 3)
            .attr("d", lineExec);

        // Add Dots (Executions)
        svg.selectAll("myCircles")
            .data(dataExecutions)
            .enter()
            .append("circle")
            .attr("fill", "#2563eb")
            .attr("stroke", "none")
            .attr("cx", (d, i) => x(labels[i]))
            .attr("cy", d => y(d))
            .attr("r", 4);

        // Legend
        const legend = svg.append("g").attr("transform", `translate(${width - 150}, -30)`);
        
        legend.append("circle").attr("cx", 0).attr("cy", 0).attr("r", 4).style("fill", "#2563eb");
        legend.append("text").attr("x", 10).attr("y", 4).text("Total Executions").style("font-size", "10px").attr("alignment-baseline", "middle");

        legend.append("circle").attr("cx", 90).attr("cy", 0).attr("r", 4).style("fill", "#9ca3af");
        legend.append("text").attr("x", 100).attr("y", 4).text("Goal").style("font-size", "10px").attr("alignment-baseline", "middle");
    }
});

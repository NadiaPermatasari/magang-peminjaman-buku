// Dashboard charts. Chart.js is loaded async, so wait for it before drawing.
(function initArgonCharts() {
  if (typeof Chart === "undefined") {
    return setTimeout(initArgonCharts, 50);
  }
  // Wait for the page (and any inline window.ARGON_CHARTS data) to be parsed.
  if (document.readyState === "loading") {
    return document.addEventListener("DOMContentLoaded", initArgonCharts);
  }

  // Pages can feed real data through window.ARGON_CHARTS = { line: {labels, data, label}, bar: {labels, data} }
  var CHARTS = window.ARGON_CHARTS || {};
  var LINE = Object.assign({ labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], data: [50, 40, 300, 220, 500, 250, 400, 230, 500], label: "Mobile apps" }, CHARTS.line || {});
  var BAR = Object.assign({ labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], data: [450, 200, 100, 220, 500, 100, 400, 230, 500] }, CHARTS.bar || {});

// chart 1

if (document.querySelector("#chart-bars")) {
  
  var ctx = document.getElementById("chart-bars").getContext("2d");
  
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: BAR.labels,
      datasets: [
        {
          label: "Sales",
          tension: 0.4,
          borderWidth: 0,
          borderRadius: 4,
          borderSkipped: false,
          backgroundColor: "#fff",
          data: BAR.data,
          maxBarThickness: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
      },
      interaction: {
        intersect: false,
        mode: "index",
      },
      scales: {
        y: {
          grid: {
            drawBorder: false,
            display: false,
            drawOnChartArea: false,
            drawTicks: false,
          },
          ticks: {
            suggestedMin: 0,
            suggestedMax: 600,
            beginAtZero: true,
            padding: 15,
            font: {
              size: 14,
              family: "Open Sans",
              style: "normal",
              lineHeight: 2,
            },
            color: "#fff",
          },
        },
        x: {
          grid: {
            drawBorder: false,
            display: false,
            drawOnChartArea: false,
            drawTicks: false,
          },
          ticks: {
            display: false,
          },
        },
      },
    },
  });
}

// chart 2

if(document.querySelector("#chart-line")){

  var ctx1 = document.getElementById("chart-line").getContext("2d");

  var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

  gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
  gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
  gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
  new Chart(ctx1, {
    type: "line",
    data: {
      labels: LINE.labels,
      datasets: [{
        label: LINE.label,
        tension: 0.4,
        borderWidth: 0,
        pointRadius: 0,
        borderColor: "#5e72e4",
        backgroundColor: gradientStroke1,
        borderWidth: 3,
        fill: true,
        data: LINE.data,
        maxBarThickness: 6

      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        }
      },
      interaction: {
        intersect: false,
        mode: 'index',
      },
      scales: {
        y: {
          grid: {
            drawBorder: false,
            display: true,
            drawOnChartArea: true,
            drawTicks: false,
            borderDash: [5, 5]
          },
          ticks: {
            display: true,
            padding: 10,
            color: '#fbfbfb',
            font: {
              size: 11,
              family: "Open Sans",
              style: 'normal',
              lineHeight: 2
            },
          }
        },
        x: {
          grid: {
            drawBorder: false,
            display: false,
            drawOnChartArea: false,
            drawTicks: false,
            borderDash: [5, 5]
          },
          ticks: {
            display: true,
            color: '#ccc',
            padding: 20,
            font: {
              size: 11,
              family: "Open Sans",
              style: 'normal',
              lineHeight: 2
            },
          }
        },
      },
    },
  });
}
})();

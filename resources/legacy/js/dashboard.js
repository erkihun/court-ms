import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("casesChart");
  if (!el) return;

  const labels = JSON.parse(el.dataset.labels || "[]");
  const values = JSON.parse(el.dataset.values || "[]");

  new Chart(el, {
    type: "line",
    data: {
      labels,
      datasets: [{ label: "New cases", data: values, tension: 0.3, fill: true }],
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: "#cbd5e1" }, grid: { display: false } },
        y: { ticks: { color: "#cbd5e1" }, grid: { color: "rgba(148,163,184,0.15)" } },
      },
    },
  });
});

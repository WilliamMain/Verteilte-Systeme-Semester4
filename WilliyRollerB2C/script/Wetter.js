
document.addEventListener('DOMContentLoaded', () => {
    const weatherContainer = document.getElementById('weatherContainer');

    // Benutzer Standort holen
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const { latitude, longitude } = position.coords;
            fetchWeatherData(latitude, longitude);
        }, error => {
            weatherContainer.innerHTML = '<p>Konnte Standort nicht finden. Bitte mach es an :(</p>';
        });
    } else {
        weatherContainer.innerHTML = '<p>Geolocation wird nicht vom Browser unterstützt</p>';
    }

    // Wetter Daten holen
    async function fetchWeatherData(latitude, longitude) {
        const apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m`;
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();
            displayWeatherData(data.hourly);
        } catch (error) {
            weatherContainer.innerHTML = '<p>Wetter Daten konnten nicht geholt werden.</p>';
        }
    }

    // Wetter Daten anzeigen
    function displayWeatherData(data) {
        const currentDate = new Date().toISOString().split('T')[0];
        const formattedDate = new Date().toLocaleDateString('de-DE', {
            day: 'numeric', month: 'long', year: 'numeric'
        });
        const weatherHtml = `
            <h2>Wetter für den ${formattedDate}</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Zeit</th>
                            <th>Temperatur (°C)</th>
                            <th>Rel. Luftfeuchtigkeit (%)</th>
                            <th>Windstärke (m/s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.time
                            .map((time, index) => {
                                if (time.startsWith(currentDate)) {
                                    return `<tr>
                                                <td>${new Date(time).toLocaleTimeString()}</td>
                                                <td>${data.temperature_2m[index]}</td>
                                                <td>${data.relative_humidity_2m[index]}</td>
                                                <td>${data.wind_speed_10m[index]}</td>
                                            </tr>`;
                                }
                                return '';
                            })
                            .join('')}
                    </tbody>
                </table>
            </div>
        `;
        weatherContainer.innerHTML = weatherHtml;
    }
});
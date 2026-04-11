<div
    x-data="{
        time: '',
        date: '',
        weather: {
            temp: '--',
            icon: '',
            description: '',
            loading: true,
            error: null
        },
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
            this.fetchWeather();
            // Refresh weather every 30 minutes
            setInterval(() => this.fetchWeather(), 30 * 60 * 1000);
        },
        updateTime() {
            const now = new Date();
            this.time = now.toLocaleTimeString('en-US', { hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit' });
            this.date = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
        },
        async fetchWeather() {
            const apiKey = '{{ $apiKey }}';
            const lat = {{ $lat }};
            const lon = {{ $lon }};
            const cacheKey = `weather_cache_${lat}_${lon}`;
            const cacheExpiry = 30 * 60 * 1000; // 30 minutes

            // Check cache
            const cached = sessionStorage.getItem(cacheKey);
            if (cached) {
                const { data, timestamp } = JSON.parse(cached);
                if (Date.now() - timestamp < cacheExpiry) {
                    this.weather = data;
                    return;
                }
            }
            
            try {
                const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`);
                const data = await response.json();
                
                if (data.cod === 200) {
                    const weatherData = {
                        temp: Math.round(data.main.temp),
                        icon: `https://openweathermap.org/img/wn/${data.weather[0].icon}.png`,
                        description: data.weather[0].description,
                        loading: false,
                        error: null
                    };
                    this.weather = weatherData;
                    
                    // Save to cache
                    sessionStorage.setItem(cacheKey, JSON.stringify({
                        data: weatherData,
                        timestamp: Date.now()
                    }));
                } else {
                    this.weather.error = 'Weather unavailable';
                    this.weather.loading = false;
                }
            } catch (error) {
                console.error('Weather fetch error:', error);
                this.weather.error = 'Error';
                this.weather.loading = false;
            }
        }
    }"
    {{ $attributes->merge(['class' => 'flex items-center gap-4']) }}>
    <!-- Date & Time -->
    <div class="flex flex-col items-end text-right pointer-events-auto">
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400" x-text="date"></span>
        <span class="text-sm font-bold text-gray-800 dark:text-white tabular-nums" x-text="time"></span>
    </div>

    <div class="h-8 w-px bg-gray-300 dark:bg-gray-600"></div>

    <!-- Weather -->
    <div class="flex items-center gap-2 min-w-[140px]">
        <div class="flex flex-col">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $municipalityName }}</span>
            <template x-if="weather.loading">
                <span class="text-xs text-gray-400">Loading...</span>
            </template>
            <template x-if="!weather.loading && !weather.error">
                <div class="flex items-center gap-1">
                    <span class="text-sm font-bold text-gray-800 dark:text-white" x-text="weather.temp + '°C'"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 capitalize" x-text="weather.description"></span>
                </div>
            </template>
            <template x-if="weather.error">
                <span class="text-xs text-red-400" x-text="weather.error"></span>
            </template>
        </div>
        <template x-if="!weather.loading && weather.icon">
            <img :src="weather.icon" alt="Weather Icon" class="w-8 h-8 drop-shadow-[0_0_1px_rgba(0,0,0,0.5)] dark:drop-shadow-none">
        </template>
    </div>
</div>
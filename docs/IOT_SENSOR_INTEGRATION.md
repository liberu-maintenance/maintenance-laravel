# IoT Sensor Integration for Equipment Condition Monitoring

This documentation describes the IoT sensor integration feature that enables real-time monitoring of equipment conditions and provides predictive maintenance insights.

## Overview

The IoT sensor integration allows maintenance teams to:
- Monitor equipment conditions in real-time
- Receive automatic alerts when readings exceed thresholds
- View historical sensor data and trends
- Get predictive maintenance insights based on data analysis
- Submit sensor data via REST API endpoints

## Architecture

### Database Schema

#### Equipment Table Extensions
- `sensor_enabled` (boolean): Whether IoT monitoring is enabled
- `sensor_type` (string): Type of sensor (temperature, vibration, pressure, etc.)
- `sensor_id` (string, unique): Unique identifier for the IoT sensor
- `sensor_config` (json): Configuration including thresholds
- `last_sensor_reading_at` (timestamp): Timestamp of last reading

#### IoT Sensor Readings Table
- `equipment_id` (foreign key): Reference to equipment
- `sensor_type` (string): Type of sensor
- `metric_name` (string): Name of the metric being measured
- `value` (decimal): The measured value
- `unit` (string): Unit of measurement
- `metadata` (json): Additional sensor metadata
- `status` (enum): normal, warning, critical, or error
- `reading_time` (timestamp): When the reading was taken

### Models

#### IotSensorReading Model
Located at: `app/Models/IotSensorReading.php`

**Relationships:**
- `belongsTo(Equipment::class)`

**Scopes:**
- `betweenDates($startDate, $endDate)` - Filter readings by date range
- `forMetric($metricName)` - Filter by metric name
- `critical()` - Get only critical readings
- `warning()` - Get only warning readings
- `recent($hours = 24)` - Get recent readings

**Methods:**
- `isAbnormal()` - Check if reading is abnormal (warning/critical/error)

#### Equipment Model Extensions
Located at: `app/Models/Equipment.php`

**New Relationships:**
- `hasMany(IotSensorReading::class)` - All sensor readings
- `latestSensorReadings()` - Last 24 hours of readings
- `criticalSensorReadings()` - Critical status readings

**New Scopes:**
- `sensorEnabled()` - Equipment with sensors enabled
- `withCriticalReadings()` - Equipment with recent critical readings

**New Methods:**
- `getHealthStatus()` - Returns: healthy, warning, critical, or unknown

### Services

#### IotSensorService
Located at: `app/Services/IotSensorService.php`

**Key Methods:**

1. `storeReading(Equipment $equipment, array $data): IotSensorReading`
   - Stores a new sensor reading
   - Automatically determines status based on thresholds
   - Updates equipment's last reading timestamp

2. `getEquipmentHealthSummary(Equipment $equipment, int $hours = 24): array`
   - Returns health summary for equipment
   - Includes metrics statistics (current, average, min, max)
   - Counts alerts

3. `getPredictiveInsights(Equipment $equipment, int $days = 30): array`
   - Analyzes trends using linear regression
   - Predicts equipment degradation
   - Returns trend direction and rate of change

4. `getRealTimeDashboardData(): array`
   - Aggregates data for dashboard
   - Counts equipment by health status
   - Lists critical equipment

### API Endpoints

All endpoints are prefixed with `/api/iot-sensors`

#### Public Endpoints (for sensor data submission)

**POST /readings**
Submit a single sensor reading.

Request body:
```json
{
  "sensor_id": "SENSOR-001",
  "sensor_type": "temperature",
  "metric_name": "temperature",
  "value": 75.5,
  "unit": "°C",
  "metadata": {
    "battery_level": "85%",
    "signal_strength": "good"
  },
  "reading_time": "2026-02-19T10:30:00Z"
}
```

Response (201 Created):
```json
{
  "success": true,
  "data": {
    "id": 123,
    "equipment_id": 45,
    "sensor_type": "temperature",
    "metric_name": "temperature",
    "value": 75.5,
    "unit": "°C",
    "status": "normal",
    "reading_time": "2026-02-19T10:30:00Z"
  },
  "message": "Sensor reading stored successfully"
}
```

**POST /readings/batch**
Submit multiple sensor readings at once.

Request body:
```json
{
  "readings": [
    {
      "sensor_id": "SENSOR-001",
      "metric_name": "temperature",
      "value": 75.5,
      "unit": "°C"
    },
    {
      "sensor_id": "SENSOR-002",
      "metric_name": "vibration",
      "value": 2.5,
      "unit": "mm/s"
    }
  ]
}
```

Response (200 OK):
```json
{
  "success": true,
  "stored_count": 2,
  "errors_count": 0,
  "errors": []
}
```

#### Protected Endpoints (require authentication)

**GET /dashboard**
Get real-time dashboard data for all monitored equipment.

Response:
```json
{
  "success": true,
  "data": {
    "total_monitored": 15,
    "healthy": 10,
    "warning": 3,
    "critical": 2,
    "no_data": 0,
    "critical_equipment": [
      {
        "id": 5,
        "name": "HVAC Unit #3",
        "location": "Building A - Floor 2",
        "last_reading": "2026-02-19T10:25:00Z"
      }
    ]
  }
}
```

**GET /equipment/{id}/health**
Get health summary for specific equipment.

Response:
```json
{
  "success": true,
  "data": {
    "status": "warning",
    "metrics": [
      {
        "name": "temperature",
        "current": 85.5,
        "average": 82.3,
        "min": 75.0,
        "max": 87.2,
        "unit": "°C",
        "readings_count": 48,
        "status": "warning"
      }
    ],
    "alerts_count": 5,
    "last_reading_at": "2026-02-19T10:30:00Z"
  }
}
```

**GET /equipment/{id}/insights**
Get predictive maintenance insights for specific equipment.

Response:
```json
{
  "success": true,
  "data": {
    "temperature": {
      "trend": "increasing",
      "direction": "increasing",
      "rate_of_change": 0.0523
    },
    "vibration": {
      "trend": "stable",
      "direction": "stable",
      "rate_of_change": 0.0012
    }
  }
}
```

## Filament UI Components

### Widgets

#### IotEquipmentMonitoringWidget
Located at: `app/Filament/App/Widgets/IotEquipmentMonitoringWidget.php`

Displays real-time statistics:
- Total monitored equipment
- Healthy equipment count
- Warning status count
- Critical status count

Refreshes every 30 seconds.

#### CriticalEquipmentAlertsWidget
Located at: `app/Filament/App/Widgets/CriticalEquipmentAlertsWidget.php`

Table widget showing equipment with critical sensor readings:
- Equipment name and location
- Critical metric details
- Alert status
- Last reading time

Refreshes every 30 seconds.

### Resources

#### IotSensorReadingResource
Located at: `app/Filament/App/Resources/IotSensorReadings/IotSensorReadingResource.php`

Provides interface to:
- Browse all sensor readings
- Filter by status, sensor type, equipment, date range
- View recent readings (24 hours)
- View abnormal readings only

Auto-refreshes every 30 seconds.

#### Equipment Resource Extensions
Located at: `app/Filament/App/Resources/Equipment/EquipmentResource.php`

Added IoT Sensor Configuration section with:
- Enable/disable sensor toggle
- Sensor type selection
- Sensor ID input
- Sensor configuration (thresholds)

Added columns to equipment table:
- IoT Sensor status badge
- Sensor type
- Last sensor reading timestamp

## Configuration

### Sensor Threshold Configuration

Thresholds are stored in the `sensor_config` JSON field on equipment records:

```json
{
  "thresholds": {
    "temperature": {
      "warning_min": 20,
      "warning_max": 80,
      "critical_min": 10,
      "critical_max": 90
    },
    "vibration": {
      "warning_max": 5.0,
      "critical_max": 8.0
    }
  }
}
```

### Supported Sensor Types

- **temperature**: Temperature sensors (°C, °F)
- **vibration**: Vibration sensors (mm/s, g)
- **pressure**: Pressure sensors (PSI, Bar, Pa)
- **humidity**: Humidity sensors (%)
- **power**: Power consumption sensors (W, kW)
- **flow**: Flow rate sensors (L/min, GPM)
- **multi-sensor**: Devices with multiple sensor types

## Usage Examples

### Setting Up Equipment for IoT Monitoring

1. Navigate to Equipment in Filament
2. Edit or create equipment
3. Expand "IoT Sensor Configuration" section
4. Toggle "Enable IoT Sensor" to ON
5. Select sensor type
6. Enter unique sensor ID
7. Configure thresholds (optional)
8. Save

### Submitting Sensor Data (cURL example)

```bash
curl -X POST https://your-domain.com/api/iot-sensors/readings \
  -H "Content-Type: application/json" \
  -d '{
    "sensor_id": "SENSOR-001",
    "metric_name": "temperature",
    "value": 75.5,
    "unit": "°C"
  }'
```

### Viewing Real-Time Dashboard

1. Navigate to Dashboard in Filament
2. View "IoT Equipment Monitoring" widget for overview
3. Check "Critical Equipment Alerts" for immediate issues
4. Data refreshes automatically every 30 seconds

### Analyzing Sensor Data

1. Navigate to "Sensor Readings" resource
2. Use filters to narrow down data:
   - Filter by status (normal/warning/critical)
   - Filter by equipment
   - Filter by date range
   - Use "Recent (24 hours)" quick filter
   - Use "Abnormal Readings" quick filter
3. Export data if needed

## Testing

Comprehensive test suites are available:

### Unit Tests
- `tests/Unit/Models/IotSensorReadingTest.php` - Model functionality
- `tests/Unit/Models/EquipmentIotSensorTest.php` - Equipment sensor features
- `tests/Unit/Services/IotSensorServiceTest.php` - Service methods

### Feature Tests
- `tests/Feature/IotSensorApiTest.php` - API endpoints

Run tests:
```bash
php artisan test --filter IotSensor
```

## Security Considerations

1. **API Authentication**: Public endpoints accept sensor data without authentication. Consider implementing:
   - API token authentication
   - IP whitelisting
   - Rate limiting

2. **Data Validation**: All incoming sensor data is validated for:
   - Required fields
   - Valid sensor IDs
   - Numeric values
   - Sensor enabled status

3. **Protected Endpoints**: Dashboard and analytics endpoints require user authentication via Sanctum.

## Performance Considerations

1. **Indexing**: Database indexes are created on:
   - `equipment_id` + `reading_time`
   - `equipment_id` + `metric_name` + `reading_time`
   - `status` + `reading_time`

2. **Polling**: Widgets poll every 30 seconds. Adjust in widget files if needed.

3. **Batch Processing**: Use batch endpoint for submitting multiple readings to reduce HTTP overhead.

4. **Data Retention**: Consider implementing data archival for old sensor readings to maintain performance.

## Troubleshooting

### Sensor readings not appearing
1. Verify equipment has `sensor_enabled = true`
2. Check `sensor_id` matches in both equipment and API request
3. Verify API endpoint is accessible
4. Check Laravel logs for errors

### Status not calculating correctly
1. Review `sensor_config` thresholds on equipment
2. Ensure threshold values are appropriate for metric
3. Check that metric_name matches threshold configuration

### Dashboard not updating
1. Verify browser allows polling (check browser console)
2. Clear cache: `php artisan cache:clear`
3. Check that equipment has recent readings (within 24 hours)

## Future Enhancements

Potential improvements for future development:

1. **Notification System**: Automatic notifications when critical thresholds are exceeded
2. **Work Order Integration**: Automatic work order creation for critical equipment
3. **Machine Learning**: Advanced predictive models for failure prediction
4. **Data Export**: Enhanced export capabilities for analysis
5. **Custom Dashboards**: User-configurable dashboard layouts
6. **WebSocket Support**: Real-time updates via WebSockets
7. **Mobile App Integration**: Native mobile app for viewing sensor data
8. **Integration with Popular IoT Platforms**: Direct integration with AWS IoT, Azure IoT Hub, etc.

## Support

For issues or questions:
1. Check this documentation
2. Review test files for usage examples
3. Check Laravel logs: `storage/logs/laravel.log`
4. Open an issue on GitHub

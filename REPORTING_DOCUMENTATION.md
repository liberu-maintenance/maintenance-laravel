# Enhanced Reporting and Analytics

## Overview

This enhancement provides comprehensive reporting capabilities to analyze maintenance activities, equipment performance, and operational efficiencies. The system generates detailed insights and actionable recommendations to optimize maintenance processes.

## Features

### 1. MaintenanceReportService

A comprehensive service class that provides analytical algorithms for maintenance data:

#### Available Methods

##### `calculateMTTR(?int $teamId, ?Carbon $startDate, ?Carbon $endDate): float`
Calculates Mean Time To Repair (MTTR) - the average time taken to complete work orders.
- **Returns**: Average hours to complete work orders
- **Use Case**: Measure response efficiency and identify bottlenecks

##### `calculateEquipmentUptime(int $equipmentId, ?Carbon $startDate, ?Carbon $endDate): float`
Calculates equipment uptime percentage.
- **Returns**: Uptime percentage (0-100)
- **Use Case**: Monitor equipment availability and reliability

##### `generateCostAnalysis(?int $teamId, ?Carbon $startDate, ?Carbon $endDate): array`
Generates comprehensive cost analysis including parts and labor costs.
- **Returns**: Array with parts_cost, labor_cost, total_cost, average_cost_per_work_order, total_work_orders
- **Use Case**: Budget planning and cost optimization

##### `getEquipmentPerformanceMetrics(?int $teamId, ?Carbon $startDate, ?Carbon $endDate): array`
Gets detailed performance metrics for each equipment.
- **Returns**: Array of equipment with work_order_count, total_cost, average_cost, uptime_percentage, failure_rate
- **Use Case**: Identify high-maintenance equipment and replacement candidates

##### `getTechnicianPerformanceMetrics(?int $teamId, ?Carbon $startDate, ?Carbon $endDate): array`
Gets performance metrics for technicians.
- **Returns**: Array with total_assigned, completed, in_progress, completion_rate, average_completion_time_hours
- **Use Case**: Evaluate workforce efficiency and workload distribution

##### `analyzeMaintenanceTrends(?int $teamId, int $days): array`
Analyzes maintenance trends over a specified period.
- **Returns**: Daily data, week-over-week changes, averages, and peak activity periods
- **Use Case**: Forecast maintenance demand and resource planning

##### `generateComprehensiveReport(?int $teamId, ?Carbon $startDate, ?Carbon $endDate): array`
Generates a complete maintenance report with all metrics and actionable insights.
- **Returns**: Complete report with all analysis sections
- **Use Case**: Executive summaries and performance reviews

### 2. Filament Widgets

#### EquipmentPerformanceWidget
Displays a table of equipment performance metrics including:
- Equipment name and serial number
- Criticality level
- Number of work orders
- Uptime percentage (color-coded)
- Total and average costs

#### MaintenanceCostAnalysisWidget
A doughnut chart visualizing cost breakdown:
- Parts costs
- Labor costs
- Total cost display

#### TechnicianPerformanceWidget
Shows technician performance metrics:
- Work orders assigned, completed, in progress, and pending
- Completion rate (color-coded)
- Average completion time

#### AdvancedMaintenanceTrendsWidget
Line chart showing 90-day maintenance trends:
- Total work orders
- Completed work orders
- Urgent priority items
- High priority items
- Week-over-week change indicator

#### ActionableInsightsWidget
Displays AI-driven recommendations based on data analysis:
- Critical alerts (red)
- Warnings (yellow)
- Informational insights (blue)
- Recommendations for each insight

### 3. Maintenance Reports Page

A comprehensive Filament page for generating custom reports:

#### Features
- **Date Range Selection**: Choose start and end dates for analysis
- **Generate Report**: Create comprehensive reports with one click
- **Summary Metrics**: Key performance indicators (MTTR, costs)
- **Equipment Performance Table**: Top 10 equipment by cost
- **Technician Performance Table**: All technicians with metrics
- **Actionable Insights**: Automated recommendations
- **Export Functionality**: Export reports to CSV format

#### Navigation
Access via: **Reports → Maintenance Reports** in the Filament app panel

### 4. Actionable Insights

The system automatically generates insights in the following categories:

#### Cost Management
- Identifies equipment exceeding $5,000 in maintenance costs
- Recommends evaluating replacement vs. repair costs

#### Equipment Reliability
- Detects equipment with uptime below 80%
- Suggests implementing preventive maintenance schedules

#### Response Time
- Alerts when MTTR exceeds 24 hours
- Recommends reviewing staffing and inventory levels

#### Preventive Maintenance
- Identifies overdue maintenance schedules
- Prioritizes preventive actions to avoid failures

#### Resource Management
- Detects technicians with low completion rates
- Suggests workload redistribution and training

## Usage Examples

### Using the Service in Code

```php
use App\Services\MaintenanceReportService;

$reportService = app(MaintenanceReportService::class);

// Calculate MTTR for the last 30 days
$mttr = $reportService->calculateMTTR(
    teamId: $team->id,
    startDate: now()->subDays(30),
    endDate: now()
);

// Get equipment performance metrics
$equipmentMetrics = $reportService->getEquipmentPerformanceMetrics($team->id);

// Generate a comprehensive report
$report = $reportService->generateComprehensiveReport(
    teamId: $team->id,
    startDate: now()->subMonth(),
    endDate: now()
);
```

### Adding Widgets to Dashboard

Widgets are automatically available in the Filament app panel. To add them to your dashboard, they're already registered in the Filament configuration.

### Customizing Reports

The service methods accept optional parameters for filtering:
- `$teamId`: Filter by team (multi-tenant support)
- `$startDate`: Beginning of analysis period
- `$endDate`: End of analysis period

## Benefits

1. **Data-Driven Decision Making**: Make informed decisions based on comprehensive analytics
2. **Cost Optimization**: Identify high-cost equipment and optimize maintenance spending
3. **Improved Uptime**: Monitor equipment availability and reduce downtime
4. **Resource Optimization**: Balance technician workloads and improve efficiency
5. **Predictive Insights**: Anticipate maintenance needs and prevent failures
6. **Compliance**: Generate reports for audits and compliance requirements

## Technical Details

### Performance Considerations
- Widgets disable polling to reduce server load
- Reports use efficient Eloquent queries with eager loading
- CSV export streams data to handle large datasets
- Metrics are calculated on-demand for real-time accuracy

### Multi-Tenancy Support
All reporting features support multi-tenancy through the team_id filter:
- Team-isolated data
- Respects Filament tenant context
- Secure data separation

### Security
- Uses Eloquent ORM to prevent SQL injection
- Filament authentication and authorization
- Team-based access control
- Input validation on date ranges

## Future Enhancements

Potential additions for future versions:
- PDF export with charts and graphs
- Scheduled report generation and email delivery
- Custom report templates
- Predictive maintenance algorithms using machine learning
- Integration with external BI tools
- Real-time dashboard refresh
- Comparison reports (period over period)
- Benchmarking against industry standards

## Support

For issues or feature requests, please create an issue in the GitHub repository.

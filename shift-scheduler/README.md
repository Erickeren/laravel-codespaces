# Laravel Shift Scheduler

A web-based shift scheduling application built with Laravel 10, designed for managing a 4-person rotation system with alternating 4 days on/off cycles.

## Features

### Core Functionality
- **4-Person System**: Exactly 4 people in the rotation
- **Dual Shifts**: Two daily shifts (Shift A: 6AM-6PM, Shift B: 6PM-6AM)
- **4-on/4-off Pattern**: Each person works 4 consecutive days, then rests 4 days
- **Automatic Rotation**: Continuous cycling ensures fair distribution
- **Two Work Groups**: 2 people on duty each day, 2 people off duty

### User Interface
- **Modern Design**: Responsive UI built with Tailwind CSS
- **Three-Step Process**: Add people → Generate schedule → Export results
- **Dual Tables**: Separate tables for on-duty and off-duty personnel
- **Date Range Selection**: Generate schedules for 1-12 weeks
- **Visual Status Indicators**: Color-coded badges for shifts and status

### Export Capabilities
- **Excel Export**: Download complete schedules as Excel files
- **Formatted Output**: Professional styling with headers and colors
- **Complete Data**: Includes dates, names, shift types, and times

## Technology Stack

- **Backend**: Laravel 10.x
- **Database**: SQLite (configurable to MySQL/PostgreSQL)
- **Frontend**: Blade templates with Tailwind CSS
- **Excel Export**: Maatwebsite/Excel package
- **Dependencies**: PHP 8.4+, Composer

## Installation

### Prerequisites
- PHP 8.4 or higher
- Composer
- PHP extensions: GD, Zip, SQLite

### Setup Steps

1. **Clone/Setup the project**
   ```bash
   # If starting fresh
   composer create-project laravel/laravel shift-scheduler "10.*"
   cd shift-scheduler
   ```

2. **Install Excel package**
   ```bash
   composer require "maatwebsite/excel:^3.1"
   ```

3. **Configure database**
   ```bash
   # Create SQLite database
   touch database/database.sqlite
   
   # Update .env file
   DB_CONNECTION=sqlite
   # Comment out other DB_ variables
   ```

4. **Run migrations**
   ```bash
   php artisan migrate
   ```

5. **Start the server**
   ```bash
   php artisan serve
   ```

6. **Access the application**
   Open your browser to: http://localhost:8000

## Database Schema

### People Table
- `id` - Primary key
- `name` - Person's name
- `timestamps` - Created/updated timestamps

### Schedules Table
- `id` - Primary key
- `date` - Schedule date
- `person_id` - Foreign key to people table
- `shift_type` - Enum: 'A' (6AM-6PM) or 'B' (6PM-6AM)
- `status` - Enum: 'on_duty' or 'off_duty'
- `timestamps` - Created/updated timestamps
- **Unique constraint**: person_id + date

## How the Rotation Works

### 8-Day Cycle Pattern
The system operates on an 8-day cycle where:

- **Days 1-4**: Group 1 (Person 1 & 2) on duty, Group 2 (Person 3 & 4) off duty
- **Days 5-8**: Group 2 (Person 3 & 4) on duty, Group 1 (Person 1 & 2) off duty

### Shift Assignments
Each duty day has two shifts:
- **Shift A**: 6:00 AM - 6:00 PM (12 hours)
- **Shift B**: 6:00 PM - 6:00 AM+1 (12 hours, overnight)

### Example Schedule
```
Day 1-4: John (Shift A), Jane (Shift B) | Bob & Alice OFF
Day 5-8: Bob (Shift A), Alice (Shift B) | John & Jane OFF
Day 9-12: John (Shift A), Jane (Shift B) | Bob & Alice OFF
...continues...
```

## Usage Instructions

### Step 1: Add People
1. Enter exactly 4 names in the "Add People" form
2. Click "Save People" to store them in the database
3. This will clear any existing schedules

### Step 2: Generate Schedule
1. Select a start date
2. Choose duration (1-12 weeks)
3. Click "Generate Schedule"
4. The system will create the rotating schedule automatically

### Step 3: View Results
- **On-Duty Table**: Shows who is working each day and their shifts
- **Off-Duty Table**: Shows who is resting each day
- **Summary Stats**: Displays key metrics about the schedule

### Step 4: Export (Optional)
- Click "Export to Excel" to download the schedule
- File includes all schedule data with professional formatting

## File Structure

```
shift-scheduler/
├── app/
│   ├── Http/Controllers/
│   │   └── ShiftController.php      # Main application logic
│   ├── Models/
│   │   ├── Person.php               # Person model
│   │   └── Schedule.php             # Schedule model
│   └── Exports/
│       └── ScheduleExport.php       # Excel export functionality
├── database/
│   ├── migrations/
│   │   ├── *_create_people_table.php
│   │   └── *_create_schedules_table.php
│   └── database.sqlite              # SQLite database file
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php            # Main layout template
│   └── shifts/
│       └── index.blade.php          # Main application interface
├── routes/
│   └── web.php                      # Application routes
└── README.md                        # This file
```

## Routes

- `GET /` - Main dashboard (shifts.index)
- `POST /people` - Store people data
- `POST /schedule/generate` - Generate new schedule
- `GET /schedule/export` - Download Excel export

## Key Features Implementation

### Automatic Rotation Logic
The rotation algorithm in `ShiftController::generateSchedule()`:
1. Uses modulo 8 to determine cycle position
2. Assigns people to groups based on cycle day
3. Creates both on-duty and off-duty records
4. Ensures continuous rotation over any time period

### Data Validation
- Exactly 4 people required
- Date validation for schedule generation
- Duration limits (1-52 weeks)
- Unique person-date combinations

### User Experience
- Real-time form validation
- Success/error flash messages
- Responsive design for mobile/desktop
- Intuitive step-by-step workflow

## Customization Options

### Modify Shift Times
Update the `getShiftTimeAttribute()` method in `Schedule.php`:
```php
public function getShiftTimeAttribute()
{
    if ($this->shift_type === 'A') {
        return 'Custom time range A';
    } else {
        return 'Custom time range B';
    }
}
```

### Change Rotation Pattern
Modify the logic in `ShiftController::generateSchedule()` to adjust:
- Number of consecutive work days
- Number of people per shift
- Shift assignment patterns

### Styling Customization
- Update Tailwind classes in `resources/views/`
- Modify color scheme in `layouts/app.blade.php`
- Add custom CSS as needed

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Ensure SQLite file exists: `touch database/database.sqlite`
   - Check .env configuration
   - Run migrations: `php artisan migrate`

2. **Excel Export Not Working**
   - Install PHP GD extension: `sudo apt-get install php-gd`
   - Verify maatwebsite/excel package is installed

3. **Blank Page or Errors**
   - Check Laravel logs: `storage/logs/laravel.log`
   - Ensure proper file permissions
   - Verify all dependencies are installed

### Development Commands

```bash
# Clear application caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Generate application key
php artisan key:generate

# Reset database
php artisan migrate:refresh

# Run in different environment
php artisan serve --host=0.0.0.0 --port=8080
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review Laravel documentation
3. Verify all requirements are met
4. Check application logs for detailed error messages

---

**Built with Laravel 10 + Tailwind CSS + Excel Export**

*A complete solution for 4-person shift rotation management*

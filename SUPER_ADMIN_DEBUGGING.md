# Data Not Displaying - Troubleshooting Guide

## Penyebab dan Solusi

### 1. **Cek API Response Terlebih Dahulu**

Kunjungi endpoint debug untuk melihat apa yang API kembalikan:
```
/super-admin/debug/api
```

Endpoint ini akan menunjukkan response dari 3 endpoint:
- `super-admin/statistics`
- `super-admin/organizers`
- `super-admin/events`

### 2. **Struktur Response yang Diharapkan**

#### Dashboard Statistics Response
```json
{
  "data": {
    "statistics": {
      "total_organizers": 10,
      "total_events": 50,
      "total_participants": 500,
      "total_revenue": 50000000
    },
    "event_status_breakdown": {
      "published": 30,
      "draft": 10,
      "completed": 8,
      "cancelled": 2
    },
    "monthly_trends": [
      {
        "month": "Jan 2024",
        "count": 5,
        "total_amount": 5000000
      }
    ],
    "top_organizers": [
      {
        "id": 1,
        "name": "Organizer Name",
        "email": "org@email.com",
        "events_count": 10
      }
    ],
    "category_breakdown": [
      {
        "name": "Technology",
        "count": 20
      }
    ]
  }
}
```

#### Organizers Response
```json
{
  "data": {
    "organizers": [
      {
        "id": 1,
        "name": "Organizer Name",
        "email": "org@email.com",
        "status": "active",
        "events_count": 10,
        "total_participants": 100,
        "published_events": 8,
        "total_revenue": 10000000,
        "event_breakdown": {
          "published": 8,
          "draft": 2,
          "completed": 5,
          "cancelled": 0
        }
      }
    ],
    "pagination": {
      "total": 10,
      "per_page": 10,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

#### Events Response
```json
{
  "data": {
    "events": [
      {
        "id": 1,
        "title": "Event Title",
        "category": "Technology",
        "organizer_name": "Organizer Name",
        "organizer_email": "organizer@email.com",
        "start_date": "2024-01-15T09:00:00",
        "status": "published",
        "participant_count": 50,
        "quota": 100,
        "approved_count": 45,
        "revenue": 5000000,
        "attendance_rate": 80,
        "description": "Event description..."
      }
    ],
    "pagination": {
      "total": 50,
      "per_page": 10,
      "current_page": 1,
      "last_page": 5
    }
  }
}
```

### 3. **Kemungkinan Masalah dan Solusi**

#### Problem A: API Returns Different Structure
**Gejala**: Data tidak muncul di dashboard
**Solusi**: 
1. Check `/super-admin/debug/api` untuk melihat struktur actual
2. Update controller untuk match struktur response sebenarnya
3. Perbarui ekstraksi data di SuperAdminController

#### Problem B: Missing Fields
**Gejala**: Beberapa field kosong atau undefined
**Solusi**:
1. Pastikan API mengembalikan semua required fields
2. Gunakan null coalescing `??` untuk default values
3. Check view template untuk field yang tidak ada

#### Problem C: Authentication Issues
**Gejala**: Error 401 atau data kosong
**Solusi**:
1. Pastikan user memiliki role `super_admin`
2. Verify API token valid di session
3. Check `Session::get('api_token')` tidak null

#### Problem D: Pagination Issues
**Gejala**: Hanya muncul beberapa item atau error pagination
**Solusi**:
1. Verify pagination response format dari API
2. Check `LengthAwarePaginator` initialization
3. Pastikan `pagination['total']` sesuai dengan actual data count

### 4. **Debug Steps**

#### Step 1: Cek API Response
```bash
# Kunjungi:
/super-admin/debug/api

# Lihat response JSON
# Copy paste struktur response yang sesungguhnya
```

#### Step 2: Cek Error Message di View
- Error message akan ditampilkan di atas content
- Lihat pesan error untuk mengetahui exception yang terjadi

#### Step 3: Cek Logs
```bash
# Lihat log file:
storage/logs/laravel.log

# Cari entry dengan "Super Admin" untuk melihat detailed error
```

#### Step 4: Test Individual Endpoints
Modify `/super-admin/debug/api` untuk test satu endpoint:

```php
// Di DebugController.php
$response = $this->api->withToken($token)->get('super-admin/statistics');
return response()->json(['response' => $response], 200, [], JSON_PRETTY_PRINT);
```

### 5. **Common Issues**

| Issue | Cause | Solution |
|-------|-------|----------|
| Data kosong | API error atau tidak ada data | Check `/super-admin/debug/api` |
| Error message muncul | Exception di controller | Baca error message dan cek logs |
| Hanya header muncul | `$stats` array kosong | Pastikan API endpoint correct |
| Partial data | Response structure tidak match | Update ekstraksi data di controller |
| Charts tidak muncul | `monthly_trends` atau `category_breakdown` kosong | Pastikan API return data untuk charts |

### 6. **Controller Data Extraction Logic**

Saat ini controller menggunakan:
```php
// Handle different response structures
$data = $statsResponse['data'] ?? $statsResponse ?? [];

// Extract dengan fallback
$statistics = $data['statistics'] ?? $data ?? [];
$eventStatusBreakdown = $data['event_status_breakdown'] ?? [
    'published' => 0,
    'draft' => 0,
    'completed' => 0,
    'cancelled' => 0
];
```

**Ini berarti:**
- Coba cari di `response['data']['statistics']` dulu
- Jika tidak ada, coba `response['data']` 
- Jika tidak ada, use empty array `[]`

### 7. **Fix Response Structure Mismatch**

Jika API return struktur berbeda, update controller:

```php
// Contoh: Jika API return response['result'] bukan response['data']
$data = $statsResponse['result'] ?? $statsResponse['data'] ?? $statsResponse ?? [];

// Contoh: Jika API return response langsung tanpa ['data'] wrapper
$statistics = $data['total_organizers'] ?? $data ?? [];
```

### 8. **Testing with Sample Data**

Untuk test view tanpa API, modify controller:
```php
$stats = [
    'total_organizers' => 10,
    'total_events' => 50,
    'total_participants' => 500,
    'total_revenue' => 50000000,
    'event_breakdown' => [
        'published' => 30,
        'draft' => 10,
        'completed' => 8,
        'cancelled' => 2
    ],
    'monthly_trends' => [],
    'top_organizers' => [],
    'category_breakdown' => [],
];
```

### 9. **Key Files to Check**

1. **Controller**: `app/Http/Controllers/SuperAdminController.php`
   - Check data extraction logic
   - Verify response handling

2. **Views**: `resources/views/admin/super/`
   - `dashboard.blade.php` - Check $stats usage
   - `organizers.blade.php` - Check $organizers usage
   - `events.blade.php` - Check $events usage

3. **Routes**: `routes/web.php`
   - Check super.admin route group
   - Verify middleware

4. **Logs**: `storage/logs/laravel.log`
   - Check for exceptions
   - See detailed error messages

### 10. **Next Steps**

1. Visit `/super-admin/debug/api` dan copy response
2. Send response ke development team
3. Update controller jika response structure berbeda
4. Clear browser cache dan refresh
5. Check laravel.log untuk errors

---

**Remember**: Error message di view akan memberikan clue pertama tentang masalahnya!

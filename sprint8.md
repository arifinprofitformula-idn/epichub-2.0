Kamu adalah senior Laravel engineer yang membantu saya membangun EPIC Hub Premium.

Konteks proyek:
EPIC Hub Premium adalah platform digital commerce berbasis Laravel Monolith untuk menjual ebook, ecourse, membership, event, bundle, digital file, dan memiliki sistem affiliate bernama EPI Channel.

Roadmap yang sudah/akan tersedia:

- Sprint 0: Laravel foundation, auth, Filament, Spatie Permission, role, super admin.
- Sprint 1: design system, layout publik, dashboard user premium.
- Sprint 2: Catalog & Product Management.
- Sprint 3: Direct checkout, orders, payments, manual bank transfer.
- Sprint 4: Entitlement / Access Delivery.
- Sprint 5: Ebook, Digital File, Bundle Delivery.
- Sprint 6: Course & Lesson.
- Sprint 7: Event Module.
- Sekarang Sprint 8: Affiliate / EPI Channel + Integrasi OMS.

Tujuan Sprint 8:
Membangun sistem EPI Channel / Affiliate sederhana dan mengintegrasikannya dengan OMS untuk:

1. Create Account EPIC Hub dari OMS.
2. Resend create account dari OMS secara idempotent.
3. Sinkronisasi password OMS → EPIC Hub.
4. Sinkronisasi password EPIC Hub → OMS.
5. Referral link EPI Channel.
6. Referral visit tracking.
7. Referral order tracking.
8. Commission generation setelah order paid.
9. Dashboard EPI Channel.
10. Admin management untuk EPI Channel, commission, dan payout manual.

Prinsip utama:

- Business first.
- Simplicity over complexity.
- Laravel monolith.
- Controller tetap tipis.
- Business logic masuk Action/Service.
- OMS integration harus aman, idempotent, dan auditable.
- Jangan simpan plaintext password.
- Jangan log plaintext password.
- Jangan log encrypted password mentah.
- Decryption key disimpan di config/env, tidak pernah ikut payload.
- Gunakan response code OMS:
  - 00 = Sukses
  - 99 = Gagal
- Jangan membuat MLM, level, binary, pairing, bonus jaringan, wallet, atau payout otomatis.
- Jangan membuat payment gateway baru.
- Jangan membuat public REST API besar di luar endpoint OMS yang diperlukan.

Flow OMS dari gambar:

A. Registrasi EPIC → Create Account EPIC Hub
OMS mengirim request ke EPIC Hub setelah registrasi EPIC di-approve.
Data dari OMS ke EPIC Hub:

1. Kode New EPIC
2. Nama New EPIC
3. Email addr New EPIC
4. No Tlp New EPIC
5. Nama EPI Store New EPIC
6. Kode EPIC pereferal / Sponsor
7. Nama EPIC pereferal / Sponsor
8. Password terenkripsi

Catatan security:

- Key decrypt disepakati dan disetting terpisah.
- Key decrypt tidak masuk ke messaging.
- EPIC Hub decrypt password hanya di memory, lalu Hash::make().
- Jangan simpan password plaintext.
- Jangan kirim password balik di response.

Response EPIC Hub ke OMS:

- 00 = Sukses
- 99 = Gagal

B. Sync Password OMS → EPIC Hub
OMS mengirim API Change Password ke EPIC Hub.
Data:

1. Kode EPIC
2. Email addr EPIC
3. Password terenkripsi

EPIC Hub:

- Validasi signature/token.
- Decrypt password.
- Update password user.
- Jangan trigger outbound sync balik ke OMS agar tidak infinite loop.
- Response 00/99.

C. Sync Password EPIC Hub → OMS
Saat user mengubah password di EPIC Hub:

- EPIC Hub mengirim API Change Password ke OMS.
- Data:
  1. Kode EPIC
  2. Email addr EPIC
  3. Password terenkripsi
- EPIC Hub menerima response dari OMS:
  - 00 = Sukses
  - 99 = Gagal
- Jika OMS gagal, log error untuk retry/manual follow-up.
- Jangan membuat infinite loop.

Scope yang BOLEH dibuat di Sprint 8:

A. Affiliate / EPI Channel Core

1. Tabel epi\_channels.
2. Tabel referral\_visits.
3. Tabel referral\_orders atau attribution table.
4. Tabel commissions.
5. Tabel commission\_payouts.
6. Tabel promo\_assets opsional jika ringan.
7. Enum EpiChannelStatus.
8. Enum CommissionStatus.
9. Enum PayoutStatus.
10. EPI Channel dashboard user.
11. Referral link generator.
12. Referral visit tracking.
13. Referral attribution ke order.
14. Commission generation setelah order paid.
15. Admin Filament:
    - EpiChannelResource
    - ReferralVisitResource read-only
    - CommissionResource
    - CommissionPayoutResource
    - PromoAssetResource jika dibuat
16. Admin approve/reject commission.
17. Admin create/manual mark payout paid.
18. Dashboard user update kecil:
    - Status EPI Channel
    - link Dashboard Penghasilan

B. OMS Integration

1. Endpoint inbound OMS Create Account / Resend.
2. Endpoint inbound OMS Change Password.
3. Outbound EPIC Hub Change Password ke OMS.
4. Middleware validasi OMS signature/token.
5. Service decrypt password OMS.
6. Service encrypt password untuk outbound ke OMS.
7. Integration logs.
8. Idempotency request\_id.
9. Response format OMS 00/99.
10. Config/env untuk OMS:
    - OMS\_INTEGRATION\_ENABLED
    - OMS\_INBOUND\_SECRET
    - OMS\_SIGNATURE\_SECRET
    - OMS\_PASSWORD\_ENCRYPTION\_KEY
    - OMS\_OUTBOUND\_CHANGE\_PASSWORD\_URL
    - OMS\_OUTBOUND\_TIMEOUT
    - OMS\_RESPONSE\_SUCCESS\_CODE=00
    - OMS\_RESPONSE\_FAILED\_CODE=99

Scope yang TIDAK BOLEH dibuat di Sprint 8:

- Jangan membuat MLM.
- Jangan membuat level jaringan.
- Jangan membuat binary tree.
- Jangan membuat pairing.
- Jangan membuat bonus generasi.
- Jangan membuat wallet internal.
- Jangan membuat payout otomatis ke bank.
- Jangan membuat KYC.
- Jangan membuat public API besar selain OMS.
- Jangan membuat marketing automation kompleks.
- Jangan membuat coupon engine.
- Jangan membuat subscription recurring.
- Jangan membuat payment gateway baru.
- Jangan membuat dashboard report besar di luar affiliate basic.

Audit wajib sebelum coding:

1. Cek User model dan role Spatie.
2. Cek register/login/password change implementation saat ini.
3. Cek Fortify/Livewire settings password update.
4. Cek Product model:
   - is\_affiliate\_enabled
   - affiliate\_commission\_type
   - affiliate\_commission\_value
5. Cek Order, OrderItem, Payment, MarkPaymentAsPaidAction.
6. Cek CreateDirectOrderAction.
7. Cek current routes/web.php dan apakah routes/api.php tersedia.
8. Cek bootstrap/app.php atau provider route registration.
9. Cek Filament resource pattern.
10. Cek dashboard user.
11. Jelaskan rencana perubahan.
12. Tampilkan daftar file yang dibuat/diubah.
13. Jangan langsung coding sebelum rencana jelas.

Database yang perlu dibuat:

A. epi\_channels
Field rekomendasi:

- id
- user\_id foreign constrained users cascadeOnDelete unique
- epic\_code string unique
- store\_name string nullable
- sponsor\_epic\_code string nullable
- sponsor\_name string nullable
- status string default active
- source string default oms
  contoh: oms, manual
- activated\_at timestamp nullable
- suspended\_at timestamp nullable
- metadata json nullable
- timestamps
- softDeletes

Catatan:

- epic\_code dari OMS adalah kode utama EPI Channel.
- User tetap user biasa, bukan akun terpisah.
- Tambahkan role affiliate ke user jika belum ada.

B. referral\_visits
Field rekomendasi:

- id
- epi\_channel\_id foreign constrained epi\_channels cascadeOnDelete
- product\_id foreign nullable constrained products nullOnDelete
- referral\_code string
- landing\_url text nullable
- source\_url text nullable
- visitor\_id string nullable
- session\_id string nullable
- ip\_address string nullable
- user\_agent text nullable
- clicked\_at timestamp nullable
- metadata json nullable
- timestamps

C. referral\_orders
Field rekomendasi:

- id
- order\_id foreign constrained orders cascadeOnDelete unique
- epi\_channel\_id foreign constrained epi\_channels cascadeOnDelete
- referral\_visit\_id foreign nullable constrained referral\_visits nullOnDelete
- buyer\_user\_id foreign nullable constrained users nullOnDelete
- status string default pending
- attributed\_at timestamp nullable
- metadata json nullable
- timestamps

D. commissions
Field rekomendasi:

- id
- epi\_channel\_id foreign constrained epi\_channels cascadeOnDelete
- referral\_order\_id foreign nullable constrained referral\_orders nullOnDelete
- order\_id foreign constrained orders cascadeOnDelete
- order\_item\_id foreign nullable constrained order\_items nullOnDelete
- product\_id foreign nullable constrained products nullOnDelete
- buyer\_user\_id foreign nullable constrained users nullOnDelete
- commission\_type string
- commission\_value decimal(15,2)
- base\_amount decimal(15,2)
- commission\_amount decimal(15,2)
- status string default pending
- approved\_by foreign nullable constrained users nullOnDelete
- approved\_at timestamp nullable
- rejected\_by foreign nullable constrained users nullOnDelete
- rejected\_at timestamp nullable
- rejection\_reason text nullable
- paid\_at timestamp nullable
- commission\_payout\_id foreign nullable constrained commission\_payouts nullOnDelete
- metadata json nullable
- timestamps
- softDeletes

E. commission\_payouts
Field rekomendasi:

- id
- epi\_channel\_id foreign constrained epi\_channels cascadeOnDelete
- payout\_number string unique
- total\_amount decimal(15,2)
- status string default draft
- notes text nullable
- paid\_by foreign nullable constrained users nullOnDelete
- paid\_at timestamp nullable
- metadata json nullable
- timestamps
- softDeletes

F. promo\_assets optional
Field rekomendasi:

- id
- title string
- description text nullable
- file\_path string nullable
- external\_url string nullable
- is\_active boolean default true
- sort\_order unsignedInteger default 0
- metadata json nullable
- timestamps

G. oms\_integration\_logs
Field rekomendasi:

- id
- direction string
  inbound / outbound
- action string
  create\_account / change\_password / outbound\_change\_password
- request\_id string nullable unique
- epic\_code string nullable
- email string nullable
- status string
  success / failed
- response\_code string nullable
  00 / 99
- http\_status unsignedSmallInteger nullable
- request\_payload json nullable
  sanitized only
- response\_payload json nullable
  sanitized only
- error\_message text nullable
- ip\_address string nullable
- user\_agent text nullable
- processed\_at timestamp nullable
- timestamps

Enums:
EpiChannelStatus:

- prospect
- qualified
- active
- suspended
- inactive

CommissionStatus:

- pending
- approved
- paid
- rejected

PayoutStatus:

- draft
- processing
- paid
- cancelled

ReferralOrderStatus:

- pending
- converted
- cancelled
- refunded

OmsIntegrationDirection:

- inbound
- outbound

OmsIntegrationStatus:

- success
- failed

Models & relationships:

EpiChannel:

- belongsTo User
- hasMany ReferralVisit
- hasMany ReferralOrder
- hasMany Commission
- hasMany CommissionPayout
- scope active
- method isActive()

ReferralVisit:

- belongsTo EpiChannel
- belongsTo Product

ReferralOrder:

- belongsTo Order
- belongsTo EpiChannel
- belongsTo ReferralVisit
- belongsTo buyer User

Commission:

- belongsTo EpiChannel
- belongsTo ReferralOrder
- belongsTo Order
- belongsTo OrderItem
- belongsTo Product
- belongsTo buyer User
- belongsTo CommissionPayout
- scope pending/approved/paid

CommissionPayout:

- belongsTo EpiChannel
- hasMany Commissions

OmsIntegrationLog:

- model biasa untuk audit sanitized payload

User:

- hasOne EpiChannel

Product:

- existing affiliate fields dipakai:
  - is\_affiliate\_enabled
  - affiliate\_commission\_type
  - affiliate\_commission\_value

Actions / Services Affiliate:

A. app/Actions/Affiliates/CreateOrUpdateEpiChannelFromOmsAction.php
Tanggung jawab:

- menerima payload OMS create account/resend.
- validasi kode EPIC, nama, email, phone, store name.
- decrypt password via OmsPasswordCipher.
- jika epic\_code sudah ada:
  - update data non-sensitive.
  - jangan duplicate user.
  - response success.
- jika email sudah ada dan belum punya EPI Channel:
  - attach EPI Channel ke user tersebut.
- jika email sudah ada dengan epic\_code berbeda:
  - return failed 99.
- buat/update user:
  - name
  - email
  - phone jika field ada
  - password hashed
- assign role customer dan affiliate jika ada.
- buat/update epi\_channel status active.
- simpan sponsor\_epic\_code dan sponsor\_name.
- log oms\_integration\_logs.
- jangan kirim password balik.

B. app/Actions/Affiliates/TrackReferralVisitAction.php
Tanggung jawab:

- menerima referral code / epic\_code.
- validasi epi\_channel active.
- simpan referral\_visit.
- set cookie:
  - epic\_ref
  - epic\_ref\_visit
  - expires 30 hari.
- jangan track self-referral jika user login adalah owner epi channel.

C. app/Actions/Affiliates/AttachReferralToOrderAction.php
Tanggung jawab:

- dipanggil saat CreateDirectOrderAction berhasil.
- baca cookie/session referral.
- validasi EPI Channel active.
- validasi bukan self-referral.
- buat referral\_order pending.
- order\_id unique supaya tidak double attribution.

D. app/Actions/Affiliates/CreateCommissionsForOrderAction.php
Tanggung jawab:

- dipanggil setelah order paid.
- cari referral\_order by order\_id.
- validasi:
  - referral\_order valid
  - order paid
  - produk affiliate\_enabled
  - bukan self-referral
  - order tidak refunded/cancelled
- loop order\_items.
- hitung komisi:
  - percentage = subtotal\_amount \* value / 100
  - fixed = value \* quantity
- create commission pending.
- idempotent:
  - order\_item\_id + epi\_channel\_id tidak duplicate.

E. app/Actions/Affiliates/ApproveCommissionAction.php

- status pending -> approved
- set approved\_by, approved\_at

F. app/Actions/Affiliates/RejectCommissionAction.php

- status pending/approved -> rejected
- set reason

G. app/Actions/Affiliates/CreateCommissionPayoutAction.php

- pilih approved commissions milik epi\_channel.
- buat payout draft/processing.
- attach commissions ke payout.

H. app/Actions/Affiliates/MarkPayoutPaidAction.php

- payout paid.
- commissions status paid.
- set paid\_at.

Actions / Services OMS:

A. app/Services/Oms/OmsPasswordCipher.php
Tanggung jawab:

- decrypt encrypted password dari OMS.
- encrypt password untuk outbound ke OMS.
- key dari config/env.
- jangan hardcode key.
- jangan log plaintext.
- kalau format encryption OMS belum final, buat method isolated agar mudah diganti.
- default format internal boleh AES-256-CBC dengan payload base64(iv):base64(ciphertext), tapi beri komentar jelas “adjust to OMS agreed encryption format”.

B. app/Services/Oms/OmsSignatureValidator.php atau Middleware VerifyOmsSignature
Tanggung jawab:

- validasi header:
  - X-OMS-Request-Id
  - X-OMS-Timestamp
  - X-OMS-Signature
- signature:
  - hash\_hmac('sha256', timestamp.request\_id.raw\_body, OMS\_SIGNATURE\_SECRET)
- reject timestamp di luar tolerance 5 menit.
- reject duplicate request\_id jika sudah sukses diproses.
- fallback token bearer hanya jika signature belum tersedia, tetapi HMAC tetap recommended.

C. app/Actions/Oms/HandleOmsCreateAccountAction.php

- wrap CreateOrUpdateEpiChannelFromOmsAction.
- response code 00/99.

D. app/Actions/Oms/HandleOmsChangePasswordInboundAction.php

- validate epic\_code + email.
- decrypt password.
- update password user.
- jangan trigger outbound sync ke OMS.
- log sanitized.
- response 00/99.

E. app/Actions/Oms/SendPasswordChangeToOmsAction.php

- dipanggil saat user change password di EPIC Hub.
- encrypt password.
- POST ke OMS\_OUTBOUND\_CHANGE\_PASSWORD\_URL.
- payload:
  - kode\_epic
  - email\_epic
  - encrypted\_password
- terima response 00/99.
- log outbound.
- jika gagal, log error dan tampilkan warning / simpan status failed untuk retry manual.
- jangan infinite loop.

OMS API routes:

Jika routes/api.php tersedia:

- POST /api/oms/epi-channel/create-account
- POST /api/oms/epi-channel/change-password

Jika routes/api.php belum ada:

- buat route file khusus routes/oms.php atau routes/api.php sesuai versi Laravel project.
- registrasikan route dengan cara yang sesuai struktur project.
- Jangan install Sanctum atau API package besar hanya untuk endpoint ini.

Controller:

- app/Http/Controllers/Oms/OmsEpiChannelController.php
  - createAccount()
  - changePassword()

Response format:
Success:
{
"response\_code": "00",
"message": "Sukses",
"data": {
"epic\_code": "...",
"email": "..."
}
}

Failure:
{
"response\_code": "99",
"message": "Gagal",
"error": "reason yang aman"
}

Catatan:

- Untuk business error, boleh HTTP 200 dengan response\_code 99 agar sesuai OMS.
- Untuk signature/auth invalid, boleh HTTP 401/403.

OMS inbound payload aliases:
Terima snake\_case utama:

- kode\_epic
- nama\_epic
- email\_epic
- no\_tlp\_epic
- nama\_epi\_store
- sponsor\_epic\_code
- sponsor\_name
- encrypted\_password

Boleh support alias dari dokumen OMS jika perlu:

- kode\_new\_epic
- nama\_new\_epic
- email\_addr\_new\_epic
- no\_tlp\_new\_epic
- nama\_epi\_store\_new\_epic
- kode\_epic\_sponsor
- nama\_epic\_sponsor
- password\_terenkripsi

Outbound EPIC → OMS:
Payload:

- kode\_epic
- email\_epic
- encrypted\_password

Integration rules:

- OMS create account/resend harus idempotent.
- OMS change password inbound tidak boleh membuat outbound call balik.
- EPIC change password outbound hanya untuk user yang punya EPI Channel.
- Jika user belum punya EPI Channel, password change lokal normal saja.
- Jangan kirim plaintext password ke log, response, email, atau notification.
- Password dari OMS harus di-hash menggunakan Hash::make().
- Jangan email password dari EPIC Hub.

Referral routes:
Public:

- GET /r/{epicCode}
  - redirect ke homepage atau product jika query product ada.
  - track referral visit.
- Support query:
  - ?product={slug}
  - ?to={url-safe internal path} jika aman

Public product link:

- /produk/{slug}?ref={epicCode}
  - track referral.
  - set cookie.

Middleware optional:

- CaptureReferralFromRequest
  - membaca ref dari query.
  - track visit.
  - set cookie.

Checkout integration:

- Update CreateDirectOrderAction:
  - setelah order created, panggil AttachReferralToOrderAction.
  - jangan gagal checkout jika referral invalid.
  - cukup abaikan referral invalid dan log ringan.
- Update MarkPaymentAsPaidAction:
  - setelah order paid + access/event flow sukses, panggil CreateCommissionsForOrderAction.
  - jika commission generation gagal, jangan rollback payment/access kecuali error fatal database.
  - log error agar admin bisa review.
  - idempotent.

Attribution rules:
Komisi diberikan jika:

- referral valid.
- EPI Channel active.
- order berhasil paid.
- produk affiliate\_enabled.
- bukan self-referral.
- order belum refunded/cancelled.
- commission belum pernah dibuat untuk order\_item tersebut.

User routes:
Auth-only:

- GET /epi-channel
  - Dashboard Penghasilan
- GET /epi-channel/links
  - daftar produk affiliate\_enabled dan referral link
- GET /epi-channel/commissions
  - daftar komisi
- GET /epi-channel/payouts
  - daftar payout
- GET /epi-channel/promo-assets
  - materi promosi jika dibuat

User UI dashboard EPI Channel:
Tampilkan:

- status EPI Channel
- epic\_code
- store\_name
- referral link utama
- total klik
- total order referral
- komisi pending
- komisi approved
- komisi paid
- produk untuk dipromosikan
- copy referral link
- empty state jika belum aktif

Jika user belum punya EPI Channel:

- tampilkan pesan:
  “Status EPI Channel Anda belum aktif. Aktivasi dilakukan melalui OMS / Admin.”

Admin Filament:

- EpiChannelResource:
  - user
  - epic\_code
  - store\_name
  - sponsor
  - status
  - source
  - activated\_at
  - actions: suspend/activate
- ReferralVisitResource:
  - read-only
- ReferralOrderResource:
  - read-only
- CommissionResource:
  - approve
  - reject
  - mark paid hanya via payout jika memungkinkan
- CommissionPayoutResource:
  - create payout from approved commissions
  - mark payout paid
- PromoAssetResource optional

Dashboard update:

- Tambahkan quick action “Dashboard Penghasilan”.
- Tambahkan status EPI Channel:
  - Aktif / Belum Aktif / Suspended.
- Jangan rombak dashboard besar.

Security:

- OMS endpoint pakai signature/token.
- Rate limit OMS endpoints.
- Redact password fields di logs.
- Admin resources tetap protected by Filament role admin/super\_admin.
- User EPI Channel dashboard hanya untuk user login.
- User tidak bisa melihat komisi user lain.
- Referral link tidak boleh memberi komisi self-referral.
- Jangan expose sponsor tree atau jaringan kompleks.

Testing:
Tambahkan feature tests jika memungkinkan:

1. OMS create account creates user + epi\_channel.
2. OMS create account resend idempotent.
3. OMS create account with same email different epic\_code fails.
4. OMS change password inbound updates password.
5. EPIC local password change sends outbound OMS request.
6. Referral visit tracked from /r/{epicCode}.
7. Checkout with referral creates referral\_order.
8. Paid order creates pending commission for affiliate-enabled product.
9. Self-referral does not create commission.
10. Mark payment paid twice does not duplicate commission.
11. Affiliate dashboard only shows own stats.
12. Admin can approve commission.
13. Payout paid changes commissions to paid.

Sebelum implementasi:

1. Audit current project state.
2. Cek apakah Sprint 7 sudah selesai/commit.
3. Cek Product affiliate fields.
4. Cek password update implementation.
5. Cek route registration API.
6. Cek MarkPaymentAsPaidAction.
7. Cek CreateDirectOrderAction.
8. Cek Filament structure.
9. Jelaskan rencana perubahan.
10. Tampilkan daftar file yang dibuat/diubah.
11. Jangan langsung coding sebelum rencana jelas.

Setelah implementasi jalankan:

- php artisan migrate
- npm run build
- php artisan test

Output akhir wajib:

1. Ringkasan Implementasi
2. File Dibuat
3. File Diubah
4. Migration yang Dibuat
5. Model/Relasi yang Dibuat
6. Actions/Services yang Dibuat
7. OMS Endpoint yang Dibuat
8. Affiliate Route yang Dibuat
9. Filament Resource yang Dibuat
10. Integrasi ke Checkout/Payment/Password Flow
11. Command yang Harus Saya Jalankan
12. ENV yang Harus Saya Set
13. Checklist Testing Manual
14. Catatan Risiko
15. Rekomendasi Commit Message


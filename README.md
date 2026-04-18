# WordPress CVE Scanner 2026-

<div align="center">

![Version](https://img.shields.io/badge/version-2.0-blue)
![Python](https://img.shields.io/badge/python-3.8+-green)
![License](https://img.shields.io/badge/license-MIT-red)
![CVEs](https://img.shields.io/badge/CVEs-20+-orange)

**WordPress Vulnerability Scanner & Explosilit Framework**

[![GitHub](https://img.shields.io/badge/GitHub-Repository-black)](https://github.com/zax-universe/Reverse-Engineering)

</div>

---

## Daftar CVE

| CVE | Plugin | CVSS | Type |
|-----|--------|------|------|
| CVE-2026-1357 | WPvivid Backup | 9.8 | RCE / Path Traversal |
| CVE-2026-0740 | Ninja Forms | 9.8 | RCE / File Upload |
| CVE-2026-1830 | Quick Playground | 8.4 | PHP Injection |
| CVE-2026-4484 | Masteriyo LMS | 9.8 | Privilege Escalation |
| CVE-2026-2631 | Datalogics Ecommerce | 9.1 | Auth Bypass |
| CVE-2026-4021 | Contest Gallery | 8.1 | Admin Registration |
| CVE-2026-1405 | Slider Future | 9.8 | File Upload RCE |
| CVE-2026-3657 | My Sticky Bar | 7.5 | SQL Injection |
| CVE-2026-1581 | wpForo Forum | 7.5 | SQL Injection |
| CVE-2026-5465 | Amelia Booking | 7.5 | IDOR |
| CVE-2026-1731 | BeyondTrust | 8.8 | Command Injection |
| CVE-2026-5425 | Social Photo Feed | 7.2 | Stored XSS |
| CVE-2026-4896 | WCFM | 7.5 | IDOR |
| CVE-2026-3666 | wpForo Forum | 8.1 | File Deletion |
|-----|--------|------|------|
| CVE-2026-3568 | MStore API | 4.3 | Privilege Escalation |
| CVE-2026-3585 | Events Calendar | 6.5 | Path Traversal |
| CVE-2026-2890 | Formidable Forms | 5.3 | Payment Bypass |
| CVE-2026-3005 | List Category Posts | 6.1 | Stored XSS |
| CVE-2026-3090 | Post SMTP | 6.1 | Reflected XSS |
| CVE-2026-1103 | AIKTP | 5.4 | Missing Authorization |
| CVE-2026-4124 | Ziggeo | 5.3 | Missing Authorization |
| CVE-2026-5711 | Post Blocks | 6.4 | Stored XSS |
| CVE-2026-3571 | Pie Register | 5.3 | Auth Bypass |

---

## Instalasi

### Clone Repository

```bash
git clone https://github.com/zax-universe/Reverse-Engineering.git
cd Reverse-Engineering
```

Install Dependencies

```bash
pip install requests
```

---

 Cara Penggunaan

Basic Usage

```bash
# Tampilkan help
python x.py --help

# Scan semua target di folder wp/
python x.py

# Scan dengan verbose
python x.py -v

# Scan dengan upload shell
python x.py --upload-shell

# Scan dengan deface
python x.py --deface

# Full scan (CVE + shell + deface)
python x.py --upload-shell --deface -v
```

Target Options

```bash
# Single target
python x.py -t https://example.com

# Target list file
python x.py -l targets.txt

# Mass scan dari folder wp/ (default)
python x.py
```

Login Testing

```bash
# Test credentials dari file
python x.py -f credentials.txt

# Login only (no CVE scan)
python x.py -f credentials.txt --no-cve
```

Performance Options

```bash
# Custom threads (default 20)
python x.py --threads 50

# Custom timeout (default 15)
python x.py --timeout 30
```

---

 Command Line Options

Option Description
-t, --target Single target URL
-l, --list Target list file
-f, --file Credentials file for login testing
--scan-cve Scan for CVEs (default: True)
--upload-shell Upload shell to vulnerable targets
--deface Deface vulnerable targets
--threads Number of threads (default: 20)
--timeout Request timeout in seconds (default: 15)
-v, --verbose Verbose output
--no-cve Disable CVE scanning

---

Contoh Penggunaan

Contoh 1: Scan Single Target

```bash
python x.py -t https://example.com -v
```

Output:

```
[+] Target reachable
[+] WordPress detected
[+] WordPress version: 6.4.2
[*] Scanning CVEs...
  [!] CVE-2026-1357 is VULNERABLE!
  [!] CVE-2026-0740 is VULNERABLE!
```

Contoh 2: Mass Scan dengan Shell Upload

```bash
python x.py --upload-shell --deface -v
```

Contoh 3: Test Login Credentials

```bash
python x.py -f wp.txt --no-cve
```

Format file credentials (wp.txt):

```text
https://target1.com:admin:password123
https://target2.com|user|pass123
target3.com/wp-login.php:admin:rahasia
```

Contoh 4: Scan dengan Proxy

```bash
# Masukkan proxy ke folder uax/proxy.txt
echo "http://127.0.0.1:8080" > uax/proxy.txt

# Jalankan scan
python x.py -v
```

---

Struktur Folder

```
Reverse-Engineering/
├── wp/
│   ├── targets1.txt
│   └── targets2.txt
├── uax/ 
│   ├── ua.txt
│   └── proxy.txt
├── sede/
│   ├── shell.php  
│   └── deface.html  
├── reports/       
│   ├── scan_*.json
│   ├── vulnerable_*.txt
│   ├── successful_*.txt
└── x.py   
```

---

Support & Kontribusi

· Issues: Laporkan bug di GitHub Issues
· Pull Request: Terbuka untuk kontribusi
· Saran: Diskusikan fitur baru

---

License
MIT License - Copyright (c) 2026

---

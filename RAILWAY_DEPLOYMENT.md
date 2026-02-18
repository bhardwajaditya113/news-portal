# News Portal Deployment Guide

## One-Click Deploy to Railway

[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app/new?template=https%3A%2F%2Fgithub.com%2Fyourrepo%2Fnews-portal)

## Manual Deployment Steps

### Prerequisites
- Railway account (railway.app)
- Git installed
- Railway CLI installed

### Step 1: Install Railway CLI
\`\`\`bash
npm i -g @railway/cli
\`\`\`

### Step 2: Login to Railway
\`\`\`bash
railway login
\`\`\`

### Step 3: Deploy
From the news_portal/main_files directory:
\`\`\`bash
railway up
\`\`\`

### Step 4: Set Environment Variables
In Railway dashboard, add:
- APP_KEY: Generate with `php artisan key:generate`
- APP_ENV: production
- APP_DEBUG: false
- DB_CONNECTION: mysql (Railway provides)

### Database
Railway automatically provisions a MySQL database. Connection details are injected as:
- DB_HOST
- DB_PORT
- DB_USERNAME
- DB_PASSWORD
- DB_DATABASE

### Logs & Monitoring
View logs:
\`\`\`bash
railway logs
\`\`\`

## Environment Variables (Set in Railway Dashboard)

\`\`\`
APP_NAME=NewsPortal
APP_ENV=production
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=false
APP_URL=https://your-deployed-url.railway.app
DB_CONNECTION=mysql
SANCTUM_STATEFUL_DOMAINS=your-deployed-url.railway.app
SESSION_DOMAIN=.your-deployed-url.railway.app
\`\`\`

## Post-Deployment
1. Run migrations: `railway run php artisan migrate`
2. Seed data (optional): `railway run php artisan db:seed`
3. Clear cache: `railway run php artisan cache:clear`

## Monitoring
- Storage: /var/log/app.log
- Database queries logged in production
- Use Railway CLI to view real-time logs

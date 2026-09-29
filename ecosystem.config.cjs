module.exports = {
    apps: [
        {
            name: process.env.PM2_APP_NAME || 'campus-tour-queue',
            script: 'artisan',
            interpreter: 'php',
            args: 'queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600',
            cwd: '/var/www/html',
            autorestart: true,
            min_uptime: '10s',
            max_restarts: 10,
            kill_timeout: 100000,
            merge_logs: true,
            out_file: '/dev/stdout',
            error_file: '/dev/stderr',
        },
    ],
};

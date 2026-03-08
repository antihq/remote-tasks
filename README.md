# Remote Tasks

Execute scripts on remote servers via SSH using Laravel's queue system.

## Setup

Configure SSH keys in `.env`:

```env
SSH_PRIVATE_KEY="your-private-key"
SSH_PUBLIC_KEY="your-public-key"
```

Start the queue worker:

```bash
php artisan queue:work
```

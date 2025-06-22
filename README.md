# Google AdSense Report

This project is a sample implementation for sending Google AdSense revenue reports via email, built with https://github.com/invokable/laravel-console-starter

## How to Obtain AdSense API Access & Refresh Tokens Using `oauth2l` (No Web Server Required)

This guide walks you through the complete process to obtain AdSense Management API access and refresh tokens without running a web server. You'll create a Google Cloud project, configure OAuth, use `oauth2l` to authorize, and store the credentials in a `.env` file.

---

### ✅ Step 1: Create a Project in Google Cloud Console

1. Go to [Google Cloud Console](https://console.cloud.google.com/).
2. Click the project dropdown in the top bar → **"New Project"**.
3. Name your project and click **Create**.

---

### ✅ Step 2: Enable the AdSense Management API

1. In the Cloud Console, navigate to **APIs & Services > Library**.
2. Search for **"AdSense Management API"**.
3. Click on it, then click **"Enable"**.

---

### ✅ Step 3: Configure OAuth Consent Screen

1. Go to **APIs & Services > OAuth consent screen**.
2. Choose **"External"** for user type, then click **Create**.
3. Fill in required app information:
    - App name, support email
    - Developer contact email
4. Under **Scopes**, click **"Add or Remove Scopes"** and include:
   ```
   https://www.googleapis.com/auth/adsense.readonly
   ```
5. Under **Test Users**, add your Google account email.
6. Save and continue to publish the consent screen.

---

### ✅ Step 4: Create OAuth Client ID

1. Go to **APIs & Services > Credentials**.
2. Click **"Create Credentials" > "OAuth Client ID"**.
3. Choose **"Desktop app"** for Application Type.
4. Name it and click **Create**.
5. Download the client credentials JSON file (e.g., `client_secret_XXX.json`) and save it securely.

---

### ✅ Step 5: Install `oauth2l` CLI

You can install `oauth2l` from the official GitHub repository:
https://github.com/google/oauth2l

---

### ✅ Step 6: Fetch Access and Refresh Tokens

Run the following command in your terminal:

```bash
oauth2l fetch \
  --scope https://www.googleapis.com/auth/adsense.readonly \
  --credentials ./client_secret_XXX.json \
  --output_format json
```

This will launch a browser asking you to authorize access with your Google account. After successful login and consent, a JSON response like below will appear:

```json
{
  "access_token": "...access-token...",
  "expiry": "2025-06-22T11:51:37.242796+09:00",
  "refresh_token": "...refresh-token...",
  "scope": "https://www.googleapis.com/auth/adsense.readonly",
  "token_type": "Bearer"
}
```

---

### ✅ Step 7: Store Tokens in `.env` File

Copy the access and refresh tokens into a `.env` file:

```dotenv
GOOGLE_CLIENT_ID=client_id_from_json
GOOGLE_CLIENT_SECRET=client_secret_from_json
GOOGLE_REDIRECT=http://localhost/

ADS_ACCESS_TOKEN=...access-token...
ADS_REFRESH_TOKEN=...refresh-token...
```

These tokens can now be used by your application or script to make authorized requests to the AdSense API. The access token will expire, but you can use the refresh token to obtain a new one programmatically.

---

### 🔒 Tips

- **Security**: Never commit `client_secret_XXX.json` or `.env` files to version control.
- **Token Refreshing**: You can use `oauth2l fetch` again or implement automatic refresh logic using the refresh token.
- **Scope Adjustments**: If you need to change scopes, update the OAuth consent screen and reauthorize.

---

### 🎉 Done!

You now have full access to the AdSense API with long-term credentials, without needing to run a web server. Use the stored tokens to authenticate your scripts or apps easily.

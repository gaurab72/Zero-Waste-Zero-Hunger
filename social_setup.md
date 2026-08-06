# Social Login API Setup Guide

To make the "Real-World" social login work, you need to obtain API keys from the respective providers and add them to `config/oauth.php`.

## 1. Google Cloud Console
1. Go to [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project.
3. Navigate to **APIs & Services > Credentials**.
4. Click **Create Credentials > OAuth client ID**.
5. Configure the Consent Screen (Internal or External).
6. Application Type: **Web application**.
7. Authorized Redirect URIs: `http://localhost/Modern_Food_Waste_System/src/social_auth.php?provider=google`.
8. Copy the **Client ID** and **Client Secret**.

## 2. Facebook for Developers
1. Go to [Meta for Developers](https://developers.facebook.com/).
2. Create an App.
3. Add **Facebook Login** product.
4. Go to **Settings > Basic** to get your **App ID** and **App Secret**.
5. In Facebook Login Settings, add Valid OAuth Redirect URIs: `http://localhost/Modern_Food_Waste_System/src/social_auth.php?provider=facebook`.

## 3. LinkedIn Developers
1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/).
2. Create an App.
3. Add the **Sign In with LinkedIn** product.
4. In **Auth** tab, find Client ID and Client Secret.
5. Add Authorized Redirect URLs: `http://localhost/Modern_Food_Waste_System/src/social_auth.php?provider=linkedin`.

---
### 🔧 Configuration File
Once you have the keys, update this file:
[oauth.php](file:///e:/Project_github/Github/Modern_Food_Waste_System/config/oauth.php)

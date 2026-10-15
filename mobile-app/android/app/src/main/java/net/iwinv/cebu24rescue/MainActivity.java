package net.iwinv.cebu24rescue;

import android.Manifest;
import android.app.Activity;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.graphics.Insets;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.view.View;
import android.view.WindowInsets;
import android.webkit.GeolocationPermissions;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;

public class MainActivity extends Activity {
    private static final int REQUEST_FILE = 1001;
    private static final int REQUEST_LOCATION = 2001;
    private static final int REQUEST_NOTIFICATIONS = 3001;
    private static final String START_URL = "https://onoffweb3.iwinv.net/";

    private WebView webView;
    private boolean askedNotifications = false;
    private ValueCallback<Uri[]> fileCallback;
    private GeolocationPermissions.Callback geoCallback;
    private String geoOrigin;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getWindow().setStatusBarColor(Color.parseColor("#111111"));
        getWindow().setNavigationBarColor(Color.parseColor("#111111"));

        webView = new WebView(this);
        webView.setBackgroundColor(Color.parseColor("#111111"));
        webView.addJavascriptInterface(new AdminBridge(this), "Cebu24Native");
        applySystemBarPadding(webView);
        setContentView(webView);

        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setGeolocationEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setMediaPlaybackRequiresUserGesture(false);

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                return handleUrl(request.getUrl());
            }
        });

        webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
                if (checkSelfPermission(Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
                    callback.invoke(origin, true, false);
                    return;
                }
                geoCallback = callback;
                geoOrigin = origin;
                requestPermissions(new String[]{
                        Manifest.permission.ACCESS_FINE_LOCATION,
                        Manifest.permission.ACCESS_COARSE_LOCATION
                }, REQUEST_LOCATION);
            }

            @Override
            public boolean onShowFileChooser(WebView view, ValueCallback<Uri[]> filePathCallback, FileChooserParams fileChooserParams) {
                if (fileCallback != null) {
                    fileCallback.onReceiveValue(null);
                }
                fileCallback = filePathCallback;
                try {
                    startActivityForResult(fileChooserParams.createIntent(), REQUEST_FILE);
                } catch (ActivityNotFoundException e) {
                    fileCallback = null;
                    return false;
                }
                return true;
            }
        });

        if (savedInstanceState != null) {
            webView.restoreState(savedInstanceState);
        } else {
            webView.loadUrl(START_URL);
        }
        if (AdminDevice.isAdmin(this)) {
            AdminBadgeSchedule.start(this);
            refreshLauncherBadge();
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (AdminDevice.isAdmin(this) && notificationsAllowed()) {
            LauncherBadge.update(this);
        }
    }

    private boolean notificationsAllowed() {
        if (Build.VERSION.SDK_INT < 33) {
            return true;
        }
        return checkSelfPermission(Manifest.permission.POST_NOTIFICATIONS) == PackageManager.PERMISSION_GRANTED;
    }

    public void onAdminMarked() {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                AdminBadgeSchedule.start(MainActivity.this);
                refreshLauncherBadge();
            }
        });
    }

    public void onAdminLoggedOut() {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                AdminBadgeSchedule.stop(MainActivity.this);
                LauncherBadge.clear(MainActivity.this);
            }
        });
    }

    private void refreshLauncherBadge() {
        if (!AdminDevice.isAdmin(this)) {
            return;
        }
        if (notificationsAllowed()) {
            LauncherBadge.update(this);
            return;
        }
        if (!askedNotifications && Build.VERSION.SDK_INT >= 33) {
            askedNotifications = true;
            requestPermissions(new String[]{Manifest.permission.POST_NOTIFICATIONS}, REQUEST_NOTIFICATIONS);
        }
    }

    private void applySystemBarPadding(View view) {
        view.setOnApplyWindowInsetsListener((v, insets) -> {
            int left;
            int top;
            int right;
            int bottom;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
                Insets bars = insets.getInsets(WindowInsets.Type.systemBars());
                left = bars.left;
                top = bars.top;
                right = bars.right;
                bottom = bars.bottom;
                v.setPadding(left, top, right, bottom);
                return new WindowInsets.Builder(insets)
                        .setInsets(WindowInsets.Type.systemBars(), Insets.NONE)
                        .build();
            }
            left = insets.getSystemWindowInsetLeft();
            top = insets.getSystemWindowInsetTop();
            right = insets.getSystemWindowInsetRight();
            bottom = insets.getSystemWindowInsetBottom();
            v.setPadding(left, top, right, bottom);
            return insets.replaceSystemWindowInsets(0, 0, 0, 0);
        });
        view.requestApplyInsets();
    }

    private boolean handleUrl(Uri uri) {
        if (uri == null || uri.getScheme() == null) {
            return false;
        }
        String scheme = uri.getScheme();
        if ("http".equals(scheme) || "https".equals(scheme)) {
            String host = uri.getHost();
            if (host != null && (host.equals("onoffweb3.iwinv.net") || host.endsWith(".iwinv.net"))) {
                return false;
            }
            try {
                startActivity(new Intent(Intent.ACTION_VIEW, uri));
            } catch (ActivityNotFoundException ignored) {
            }
            return true;
        }
        if ("tel".equals(scheme) || "mailto".equals(scheme) || "sms".equals(scheme)) {
            try {
                startActivity(new Intent(Intent.ACTION_VIEW, uri));
            } catch (ActivityNotFoundException ignored) {
            }
            return true;
        }
        return false;
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == REQUEST_NOTIFICATIONS && notificationsAllowed()) {
            LauncherBadge.update(this);
        }
        if (requestCode == REQUEST_LOCATION && geoCallback != null) {
            boolean granted = grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED;
            geoCallback.invoke(geoOrigin, granted, false);
            geoCallback = null;
            geoOrigin = null;
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == REQUEST_FILE && fileCallback != null) {
            fileCallback.onReceiveValue(WebChromeClient.FileChooserParams.parseResult(resultCode, data));
            fileCallback = null;
        }
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        if (webView != null) {
            webView.saveState(outState);
        }
    }

    @Override
    public void onBackPressed() {
        if (webView != null && webView.canGoBack()) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }
}

package net.iwinv.cebu24rescue;

import android.webkit.JavascriptInterface;

/** Keeps native background alerts in sync with the web administrator session. */
public final class AdminBridge {
    private final MainActivity activity;

    public AdminBridge(MainActivity activity) {
        this.activity = activity;
    }

    @JavascriptInterface
    public void markAdmin() {
        AdminDevice.setAdmin(activity, true);
        activity.onAdminMarked();
    }

    @JavascriptInterface
    public void clearAdmin() {
        AdminDevice.setAdmin(activity, false);
        activity.onAdminLoggedOut();
    }

    @JavascriptInterface
    public boolean isAdmin() {
        return AdminDevice.isAdmin(activity);
    }
}

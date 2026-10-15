package net.iwinv.cebu24rescue;

import android.content.Context;

/** This phone has signed in as the dispatcher. Customer phones stay false. */
public final class AdminDevice {
    private static final String PREF = "cebu24_admin";
    private static final String KEY = "is_admin";

    private AdminDevice() {
    }

    public static boolean isAdmin(Context context) {
        return context.getApplicationContext()
                .getSharedPreferences(PREF, Context.MODE_PRIVATE)
                .getBoolean(KEY, false);
    }

    public static void setAdmin(Context context, boolean value) {
        android.content.SharedPreferences.Editor editor = context.getApplicationContext()
                .getSharedPreferences(PREF, Context.MODE_PRIVATE)
                .edit()
                .putBoolean(KEY, value);
        if (!value) {
            editor.remove("alerted_count");
        }
        editor.apply();
    }
}

package net.iwinv.cebu24rescue;

import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Build;
import android.service.notification.StatusBarNotification;

import androidx.work.Constraints;
import androidx.work.Data;
import androidx.work.ExistingWorkPolicy;
import androidx.work.NetworkType;
import androidx.work.OneTimeWorkRequest;
import androidx.work.WorkManager;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.util.concurrent.TimeUnit;

/**
 * One app notification so Samsung One UI can show a launcher number.
 * The icon shows 1 because there is a single notification. The text has the real total.
 */
public final class LauncherBadge {

    private static final String COUNT_URL =
            "https://onoffweb3.iwinv.net/plugin/onoff-builder-bridge/api/launcher-badge.php";
    private static final String CHANNEL_ID = "cebu24_launcher_badge";
    private static final int NOTIFICATION_ID = 2401;
    private static final long MIN_INTERVAL_MS = 15000L;
    private static final String PREF = "cebu24_admin";
    private static final String KEY_ALERTED = "alerted_count";
    private static final String REMIND_WORK = "cebu24-admin-remind";
    private static final long REMIND_SECONDS = 150L;

    private static long lastAttemptAt = 0L;

    private LauncherBadge() {
    }

    public static void update(Context context) {
        if (!AdminDevice.isAdmin(context)) {
            return;
        }
        final Context app = context.getApplicationContext();
        long now = android.os.SystemClock.elapsedRealtime();
        synchronized (LauncherBadge.class) {
            if (now - lastAttemptAt < MIN_INTERVAL_MS) {
                return;
            }
            lastAttemptAt = now;
        }
        new Thread(new Runnable() {
            @Override
            public void run() {
                refresh(app);
            }
        }, "cebu24-launcher-badge").start();
    }

    /** Used by the background check. Caller is already off the main thread. */
    public static void refresh(Context context) {
        refresh(context, false);
    }

    /**
     * @param reminder true when the 2.5 minute reminder fired. That pass rings again
     *                 only if the dispatcher has not tapped the notification yet.
     */
    public static void refresh(Context context, boolean reminder) {
        if (!AdminDevice.isAdmin(context)) {
            clear(context);
            return;
        }
        Integer count = fetchCount();
        if (count == null) {
            return;
        }
        Context app = context.getApplicationContext();
        if (count < 1) {
            clear(app);
            setAlerted(app, 0);
            return;
        }
        int seen = alertedCount(app);
        boolean showing = isShowing(app);
        if (count > seen) {
            publish(app, count);
            setAlerted(app, count);
            scheduleReminder(app, true);
            return;
        }
        if (count < seen) {
            setAlerted(app, count);
        }
        if (!showing) {
            cancelReminder(app);
            return;
        }
        if (reminder) {
            publish(app, count);
            scheduleReminder(app, true);
            return;
        }
        scheduleReminder(app, false);
    }

    private static int alertedCount(Context context) {
        return context.getSharedPreferences(PREF, Context.MODE_PRIVATE).getInt(KEY_ALERTED, 0);
    }

    private static void setAlerted(Context context, int count) {
        context.getSharedPreferences(PREF, Context.MODE_PRIVATE)
                .edit()
                .putInt(KEY_ALERTED, Math.max(0, count))
                .apply();
    }

    private static boolean isShowing(Context context) {
        NotificationManager manager = (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);
        if (manager == null) {
            return false;
        }
        StatusBarNotification[] active = manager.getActiveNotifications();
        for (int i = 0; i < active.length; i++) {
            if (active[i].getId() == NOTIFICATION_ID) {
                return true;
            }
        }
        return false;
    }

    private static void scheduleReminder(Context context, boolean reset) {
        Constraints constraints = new Constraints.Builder()
                .setRequiredNetworkType(NetworkType.CONNECTED)
                .build();
        Data data = new Data.Builder().putBoolean("reminder", true).build();
        OneTimeWorkRequest request = new OneTimeWorkRequest.Builder(BadgeWorker.class)
                .setInitialDelay(REMIND_SECONDS, TimeUnit.SECONDS)
                .setConstraints(constraints)
                .setInputData(data)
                .build();
        WorkManager.getInstance(context).enqueueUniqueWork(
                REMIND_WORK,
                reset ? ExistingWorkPolicy.REPLACE : ExistingWorkPolicy.KEEP,
                request);
    }

    private static void cancelReminder(Context context) {
        WorkManager.getInstance(context).cancelUniqueWork(REMIND_WORK);
    }

    public static void clear(Context context) {
        NotificationManager manager = (NotificationManager) context.getApplicationContext()
                .getSystemService(Context.NOTIFICATION_SERVICE);
        if (manager != null) {
            manager.cancel(NOTIFICATION_ID);
        }
        publishSamsungBadge(context.getApplicationContext(), 0);
        cancelReminder(context.getApplicationContext());
    }

    private static Integer fetchCount() {
        HttpURLConnection connection = null;
        try {
            connection = (HttpURLConnection) new URL(COUNT_URL).openConnection();
            connection.setConnectTimeout(4000);
            connection.setReadTimeout(4000);
            connection.setRequestMethod("GET");
            connection.setRequestProperty("Accept", "application/json");
            int status = connection.getResponseCode();
            InputStream stream = status >= 200 && status < 300
                    ? connection.getInputStream()
                    : connection.getErrorStream();
            if (stream == null) {
                return null;
            }
            BufferedReader reader = new BufferedReader(new InputStreamReader(stream, StandardCharsets.UTF_8));
            StringBuilder body = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                body.append(line);
            }
            reader.close();
            if (status < 200 || status >= 300) {
                return null;
            }
            return Math.max(0, new JSONObject(body.toString()).optInt("count", 0));
        } catch (Exception ignored) {
            return null;
        } finally {
            if (connection != null) {
                connection.disconnect();
            }
        }
    }

    private static void publish(Context context, int count) {
        NotificationManager manager = (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);
        if (manager == null) {
            return;
        }
        if (count < 1) {
            manager.cancel(NOTIFICATION_ID);
            publishSamsungBadge(context, 0);
            return;
        }
        manager.cancel(NOTIFICATION_ID);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID,
                    "\uC0C8 \uC811\uC218",
                    NotificationManager.IMPORTANCE_DEFAULT);
            channel.setShowBadge(true);
            manager.createNotificationChannel(channel);
        }

        Intent launch = new Intent(context, MainActivity.class);
        launch.setAction(Intent.ACTION_VIEW);
        launch.setData(Uri.parse("https://onoffweb3.iwinv.net/"));
        launch.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
        int pendingFlags = PendingIntent.FLAG_UPDATE_CURRENT;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            pendingFlags |= PendingIntent.FLAG_IMMUTABLE;
        }
        PendingIntent content = PendingIntent.getActivity(context, 0, launch, pendingFlags);

        String title = context.getString(R.string.app_name);
        Notification.Builder builder = Build.VERSION.SDK_INT >= Build.VERSION_CODES.O
                ? new Notification.Builder(context, CHANNEL_ID)
                : new Notification.Builder(context);
        builder.setSmallIcon(R.drawable.ic_stat_notify)
                .setContentTitle(title)
                .setContentText("\uC0C8 \uC811\uC218 " + count + "\uAC74")
                .setAutoCancel(true)
                .setOnlyAlertOnce(false)
                .setDefaults(Notification.DEFAULT_SOUND)
                .setContentIntent(content)
                .setCategory(Notification.CATEGORY_STATUS);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            builder.setNumber(1);
        }
        manager.notify(NOTIFICATION_ID, builder.build());
        publishSamsungBadge(context, 1);
    }

    private static void publishSamsungBadge(Context context, int count) {
        Intent intent = new Intent("android.intent.action.BADGE_COUNT_UPDATE");
        intent.putExtra("badge_count", Math.max(0, count));
        intent.putExtra("badge_count_package_name", context.getPackageName());
        intent.putExtra("badge_count_class_name", MainActivity.class.getName());
        context.sendBroadcast(intent);
    }
}

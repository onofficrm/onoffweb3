package net.iwinv.cebu24rescue;

import android.content.Context;

import androidx.annotation.NonNull;
import androidx.work.Worker;
import androidx.work.WorkerParameters;

/** Checks for new customer requests while the dispatcher phone is not on screen. */
public class BadgeWorker extends Worker {
    public BadgeWorker(@NonNull Context context, @NonNull WorkerParameters params) {
        super(context, params);
    }

    @NonNull
    @Override
    public Result doWork() {
        boolean reminder = getInputData().getBoolean("reminder", false);
        LauncherBadge.refresh(getApplicationContext(), reminder);
        return Result.success();
    }
}

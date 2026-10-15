import SwiftUI

@main
struct Cebu24RescueApp: App {
    var body: some Scene {
        WindowGroup {
            WebAppView()
                .ignoresSafeArea()
                .preferredColorScheme(.dark)
        }
    }
}

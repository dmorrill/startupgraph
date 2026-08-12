import SwiftUI

@main
struct StartupGraphApp: App {
    @State private var session = AppSession()

    var body: some Scene {
        WindowGroup {
            Group {
                if session.isSignedIn {
                    MainTabView()
                } else {
                    SignInView()
                }
            }
            .environment(session)
        }
    }
}

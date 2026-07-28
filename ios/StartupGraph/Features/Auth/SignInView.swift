import SwiftUI

struct SignInView: View {
    @Environment(AppSession.self) private var session

    @State private var server = "http://127.0.0.1:8000"
    @State private var token = ""
    @State private var isConnecting = false
    @State private var errorMessage: String?

    var body: some View {
        NavigationStack {
            Form {
                Section {
                    VStack(alignment: .leading, spacing: 8) {
                        Text("StartupGraph")
                            .font(.largeTitle.bold())
                        Text("Your agent researches. You read.")
                            .foregroundStyle(.secondary)
                    }
                    .listRowBackground(Color.clear)
                    .padding(.vertical)
                }

                Section("Server") {
                    TextField("https://startupgraph.dev", text: $server)
                        .textContentType(.URL)
                        .keyboardType(.URL)
                        .autocapitalization(.none)
                        .autocorrectionDisabled()
                }

                Section {
                    SecureField("Paste your API token", text: $token)
                } header: {
                    Text("API token")
                } footer: {
                    Text("Issue one with `php artisan api:token you@example.com --name=iphone`.")
                }

                if let errorMessage {
                    Section {
                        Text(errorMessage).foregroundStyle(.red)
                    }
                }

                Section {
                    Button {
                        connect()
                    } label: {
                        if isConnecting {
                            ProgressView()
                        } else {
                            Text("Connect")
                        }
                    }
                    .disabled(token.isEmpty || server.isEmpty || isConnecting)
                }
            }
        }
    }

    private func connect() {
        isConnecting = true
        errorMessage = nil
        Task {
            defer { isConnecting = false }
            do {
                try await session.signIn(server: server, token: token)
            } catch {
                errorMessage = error.localizedDescription
            }
        }
    }
}

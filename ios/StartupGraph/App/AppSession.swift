import Foundation
import Observation

/// Connection state for the app: which backend, which token, and the
/// API client built from them. Token is persisted in the Keychain,
/// server URL in UserDefaults.
@Observable
final class AppSession {
    private(set) var serverURL: URL?
    private(set) var client: APIClient?
    private(set) var me: Me?

    var isSignedIn: Bool { client != nil }

    init() {
        if let urlString = UserDefaults.standard.string(forKey: "serverURL"),
           let url = URL(string: urlString),
           let token = Keychain.read(key: "apiToken") {
            serverURL = url
            client = APIClient(baseURL: url, token: token)
        }
    }

    /// Validates the credentials against /api/me before persisting them.
    func signIn(server: String, token: String) async throws {
        guard let url = URL(string: server.trimmingCharacters(in: .whitespacesAndNewlines)) else {
            throw APIError.invalidURL
        }

        let candidate = APIClient(baseURL: url, token: token.trimmingCharacters(in: .whitespacesAndNewlines))
        let me = try await candidate.me()

        UserDefaults.standard.set(url.absoluteString, forKey: "serverURL")
        Keychain.save(key: "apiToken", value: token)

        self.serverURL = url
        self.client = candidate
        self.me = me
    }

    func signOut() {
        Keychain.delete(key: "apiToken")
        UserDefaults.standard.removeObject(forKey: "serverURL")
        client = nil
        serverURL = nil
        me = nil
    }
}

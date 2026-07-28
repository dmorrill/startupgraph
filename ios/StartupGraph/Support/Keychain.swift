import Foundation
import Security

/// Minimal Keychain wrapper for the API token.
enum Keychain {
    private static func query(for key: String) -> [String: Any] {
        [
            kSecClass as String: kSecClassGenericPassword,
            kSecAttrService as String: "dev.startupgraph.app",
            kSecAttrAccount as String: key,
        ]
    }

    static func save(key: String, value: String) {
        delete(key: key)
        var attributes = query(for: key)
        attributes[kSecValueData as String] = Data(value.utf8)
        SecItemAdd(attributes as CFDictionary, nil)
    }

    static func read(key: String) -> String? {
        var attributes = query(for: key)
        attributes[kSecReturnData as String] = true
        attributes[kSecMatchLimit as String] = kSecMatchLimitOne

        var result: AnyObject?
        guard SecItemCopyMatching(attributes as CFDictionary, &result) == errSecSuccess,
              let data = result as? Data else { return nil }
        return String(data: data, encoding: .utf8)
    }

    static func delete(key: String) {
        SecItemDelete(query(for: key) as CFDictionary)
    }
}

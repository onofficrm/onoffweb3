import SwiftUI
import UIKit
import UserNotifications
import WebKit

struct WebAppView: UIViewRepresentable {
    private let startURL = URL(string: "https://onoffweb3.iwinv.net/")!

    func makeCoordinator() -> Coordinator {
        Coordinator()
    }

    func makeUIView(context: Context) -> WKWebView {
        let contentController = WKUserContentController()
        contentController.add(context.coordinator, name: "cebu24Admin")

        let initialAdmin = UserDefaults.standard.bool(forKey: Coordinator.adminKey)
        let bridgeScript = """
        (function () {
          var isAdminDevice = \(initialAdmin ? "true" : "false");
          window.Cebu24Native = {
            markAdmin: function () {
              isAdminDevice = true;
              window.webkit.messageHandlers.cebu24Admin.postMessage("markAdmin");
            },
            clearAdmin: function () {
              isAdminDevice = false;
              window.webkit.messageHandlers.cebu24Admin.postMessage("clearAdmin");
            },
            isAdmin: function () {
              return isAdminDevice;
            }
          };
        })();
        """
        contentController.addUserScript(
            WKUserScript(
                source: bridgeScript,
                injectionTime: .atDocumentStart,
                forMainFrameOnly: true
            )
        )

        let configuration = WKWebViewConfiguration()
        configuration.userContentController = contentController
        configuration.allowsInlineMediaPlayback = true
        configuration.websiteDataStore = .default()

        let webView = WKWebView(frame: .zero, configuration: configuration)
        webView.navigationDelegate = context.coordinator
        webView.uiDelegate = context.coordinator
        webView.backgroundColor = UIColor(red: 17 / 255, green: 17 / 255, blue: 17 / 255, alpha: 1)
        webView.isOpaque = false
        webView.scrollView.contentInsetAdjustmentBehavior = .never
        webView.allowsBackForwardNavigationGestures = true
        webView.load(URLRequest(url: startURL))
        return webView
    }

    func updateUIView(_ webView: WKWebView, context: Context) {}

    static func dismantleUIView(_ webView: WKWebView, coordinator: Coordinator) {
        webView.configuration.userContentController.removeScriptMessageHandler(forName: "cebu24Admin")
    }

    final class Coordinator: NSObject, WKNavigationDelegate, WKUIDelegate, WKScriptMessageHandler {
        static let adminKey = "cebu24_is_admin"

        func userContentController(
            _ userContentController: WKUserContentController,
            didReceive message: WKScriptMessage
        ) {
            guard message.name == "cebu24Admin", let action = message.body as? String else {
                return
            }
            if action == "markAdmin" {
                UserDefaults.standard.set(true, forKey: Self.adminKey)
            } else if action == "clearAdmin" {
                UserDefaults.standard.set(false, forKey: Self.adminKey)
                UNUserNotificationCenter.current().removeAllDeliveredNotifications()
                UNUserNotificationCenter.current().removeAllPendingNotificationRequests()
            }
        }

        func webView(
            _ webView: WKWebView,
            decidePolicyFor navigationAction: WKNavigationAction,
            decisionHandler: @escaping (WKNavigationActionPolicy) -> Void
        ) {
            guard let url = navigationAction.request.url else {
                decisionHandler(.cancel)
                return
            }

            let scheme = url.scheme?.lowercased() ?? ""
            if scheme == "tel" || scheme == "mailto" {
                UIApplication.shared.open(url)
                decisionHandler(.cancel)
                return
            }

            if navigationAction.targetFrame == nil || !isAppHost(url.host) {
                if scheme == "http" || scheme == "https" {
                    UIApplication.shared.open(url)
                    decisionHandler(.cancel)
                    return
                }
            }

            decisionHandler(.allow)
        }

        func webView(
            _ webView: WKWebView,
            createWebViewWith configuration: WKWebViewConfiguration,
            for navigationAction: WKNavigationAction,
            windowFeatures: WKWindowFeatures
        ) -> WKWebView? {
            if let url = navigationAction.request.url {
                UIApplication.shared.open(url)
            }
            return nil
        }

        private func isAppHost(_ host: String?) -> Bool {
            guard let host = host?.lowercased() else { return false }
            return host == "onoffweb3.iwinv.net" || host.hasSuffix(".iwinv.net")
        }
    }
}

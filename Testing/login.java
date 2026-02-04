package webdriver;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;

public class webdriver {
    public static void main(String[] args) {
        // Set path to chromedriver
        System.setProperty("webdriver.chrome.driver", "C:/selenium/chromedriver.exe");

        WebDriver driver = new ChromeDriver();

        try {
            // 1️⃣ Open login page
            driver.get("http://localhost/boi/login.php");

            // 2️⃣ Fill login form
            WebElement emailInput = driver.findElement(By.name("email"));
            WebElement passwordInput = driver.findElement(By.name("password"));

            // Enter test credentials
            emailInput.sendKeys("abc@gmail.com");
            passwordInput.sendKeys("12345678");

            // Submit form
            passwordInput.submit(); // or driver.findElement(By.id("loginButton")).click();

            // 3️⃣ Wait a few seconds for redirect
            Thread.sleep(3000);

            // 4️⃣ Verify login by checking URL or session page
            String currentUrl = driver.getCurrentUrl();
            System.out.println("Current URL after login: " + currentUrl);

            // Optional: check for welcome message / username
            try {
                WebElement welcome = driver.findElement(By.id("welcomeMessage")); // adjust selector
                System.out.println("Login successful! Message: " + welcome.getText());
            } catch (Exception e) {
                System.out.println("Login message not found, check login manually.");
            }

        } catch (Exception e) {
            e.printStackTrace();
        } //finally {
            // Close browser
           // driver.quit();
       // }
    }
}

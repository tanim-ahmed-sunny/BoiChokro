package webdriver;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;

public class webdriver {
    public static void main(String[] args) {
        // Set path to chromedriver if not in PATH
        System.setProperty("webdriver.chrome.driver", "C:/selenium/chromedriver.exe");

        WebDriver driver = new ChromeDriver();

        try {
            // Open registration page
            driver.get("http://localhost/boi/register.php");

            // Fill out the registration form
            WebElement usernameInput = driver.findElement(By.name("username"));
            WebElement emailInput = driver.findElement(By.name("email"));
            WebElement passwordInput = driver.findElement(By.name("password"));
            WebElement confirmPasswordInput = driver.findElement(By.name("confirm_password"));

            usernameInput.sendKeys("TestUserJava");
            emailInput.sendKeys("testuserjava@example.com");
            passwordInput.sendKeys("Test@123");
            confirmPasswordInput.sendKeys("Test@123");

            // Submit the form
            confirmPasswordInput.submit(); // presses Enter

            // Wait a few seconds for redirect (optional: use WebDriverWait for production)
            Thread.sleep(3000);

            // Check the URL after registration
            String currentUrl = driver.getCurrentUrl();
            System.out.println("Current URL after registration: " + currentUrl);

        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            // Close browser
            driver.quit();
        }
    }
}

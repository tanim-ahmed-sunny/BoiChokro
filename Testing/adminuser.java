import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;

public class AdminUsersPageTest {

    public static void main(String[] args) {

        System.setProperty("webdriver.chrome.driver",
                "C:\\selenium\\chromedriver.exe");

        WebDriver driver = new ChromeDriver();

        try {
            WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(15));

            // 1️⃣ Login page
            driver.get("http://localhost/boi/login.php");
            driver.manage().window().maximize();

            // Email
            WebElement email = wait.until(
                    ExpectedConditions.visibilityOfElementLocated(By.id("email"))
            );
            email.sendKeys("admin@boichokro.com");

            // Password
            WebElement password = driver.findElement(By.id("password"));
            password.sendKeys("admin123");

            // Sign In button
            WebElement signInBtn = wait.until(
                    ExpectedConditions.elementToBeClickable(By.xpath("//button[@type='submit']"))
            );

            ((JavascriptExecutor) driver).executeScript("arguments[0].scrollIntoView(true);", signInBtn);
            Thread.sleep(500);

            try {
                signInBtn.click();
            } catch (Exception e) {
                ((JavascriptExecutor) driver).executeScript("arguments[0].click();", signInBtn);
            }

            // Wait for dashboard
            wait.until(ExpectedConditions.urlContains("dashboard"));

            System.out.println("✅ Login successful");

            // 2️⃣ Go to Users tab
            driver.get("http://localhost/boi/admin/dashboard.php?tab=users");

            // Wait until users table or some element loads
            wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".table")));

            System.out.println("✅ Users tab loaded successfully");

            Thread.sleep(5000); // Keep browser open to check manually

        } catch (Exception e) {
            e.printStackTrace();
        }

        // 🔴 Commented for manual observation
        // driver.quit();
    }
}

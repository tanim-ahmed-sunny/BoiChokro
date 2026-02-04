import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;

public class webdriver {

    public static void main(String[] args) {

        System.setProperty("webdriver.chrome.driver",
                "C:\\selenium\\chromedriver.exe");

        WebDriver driver = new ChromeDriver();

        try {
            driver.get("http://localhost/boi/login.php");
            driver.manage().window().maximize();

            WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(15));

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
                    ExpectedConditions.elementToBeClickable(
                            By.xpath("//button[@type='submit']")
                    )
            );

            // Scroll into view (important)
            ((JavascriptExecutor) driver)
                    .executeScript("arguments[0].scrollIntoView(true);", signInBtn);

            Thread.sleep(1000);

            try {
                // Normal click
                signInBtn.click();
            } catch (Exception e) {
                // Force JS click
                ((JavascriptExecutor) driver)
                        .executeScript("arguments[0].click();", signInBtn);
            }

            // Wait for dashboard
            wait.until(ExpectedConditions.urlContains("dashboard"));

            System.out.println("✅ Login done automatically");

            Thread.sleep(5000);

        } catch (Exception e) {
            e.printStackTrace();
        }

        // 🔴 Comment for testing
        // driver.quit();
    }
}
